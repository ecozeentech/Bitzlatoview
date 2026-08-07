<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\NftBid;
use App\Models\NftCollection;
use App\Models\NftItem;
use App\Models\NftListing;
use App\Models\WalletAccount;
use App\Services\LedgerService;
use App\Support\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NftController extends Controller
{
    public function index()
    {
        $collections = NftCollection::orderByDesc('volume')->get();

        return view('app.nft.index', compact('collections'));
    }

    public function showCollection(NftCollection $collection)
    {
        $collection->load('items.owner');

        return view('app.nft.collection', compact('collection'));
    }

    public function showItem(NftItem $item)
    {
        $item->load('collection', 'owner', 'bids.bidder');

        return view('app.nft.item', compact('item'));
    }

    public function myNfts()
    {
        $items = NftItem::where('owner_user_id', Auth::id())->with('collection')->get();

        return view('app.nft.my-nfts', compact('items'));
    }

    public function buy(NftItem $item, LedgerService $ledger)
    {
        $user = Auth::user();
        abort_unless($item->is_listed, 400, 'This NFT is not currently listed.');
        abort_if($item->owner_user_id === $user->id, 400, 'You already own this NFT.');

        $wallet = WalletAccount::firstOrCreate(['user_id' => $user->id, 'type' => WalletAccount::TYPE_PRIMARY]);
        $usdt = Asset::where('symbol', 'USDT')->firstOrFail();
        $house = House::wallet(WalletAccount::TYPE_PRIMARY);
        $price = $item->price ?? 0;

        if ($price > 0) {
            $sellerWallet = $item->owner_user_id
                ? WalletAccount::firstOrCreate(['user_id' => $item->owner_user_id, 'type' => WalletAccount::TYPE_PRIMARY])
                : $house;

            try {
                $ledger->post(
                    entries: [
                        ['wallet_account_id' => $wallet->id, 'asset_id' => $usdt->id, 'direction' => 'debit', 'amount' => $price],
                        ['wallet_account_id' => $sellerWallet->id, 'asset_id' => $usdt->id, 'direction' => 'credit', 'amount' => $price],
                    ],
                    referenceType: 'nft_purchase',
                    referenceId: $item->id,
                    description: "Purchased NFT {$item->name}",
                    createdBy: $user,
                );
            } catch (\RuntimeException $e) {
                return back()->with('error', 'Insufficient USDT balance to purchase this NFT.');
            }
        }

        $item->update(['owner_user_id' => $user->id, 'is_listed' => false]);
        NftListing::where('nft_item_id', $item->id)->where('status', 'active')->update(['status' => 'sold']);

        AuditLog::record($user, 'nft.purchased', NftItem::class, $item->id);

        return back()->with('success', "You now own {$item->name}.");
    }

    public function list(Request $request, NftItem $item)
    {
        abort_unless($item->owner_user_id === Auth::id(), 403);

        $data = $request->validate(['price' => ['required', 'numeric', 'gt:0']]);

        $item->update(['price' => $data['price'], 'is_listed' => true]);
        NftListing::create(['nft_item_id' => $item->id, 'seller_id' => Auth::id(), 'price' => $data['price'], 'status' => 'active']);

        return back()->with('success', 'NFT listed for sale.');
    }

    public function bid(Request $request, NftItem $item)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0']]);

        NftBid::create($data + ['nft_item_id' => $item->id, 'bidder_id' => Auth::id(), 'status' => 'active']);

        AuditLog::record(Auth::user(), 'nft.bid_placed', NftItem::class, $item->id);

        return back()->with('success', 'Bid placed. Settlement happens on Bitzlatoview\'s internal ledger — this does not yet settle on-chain.');
    }
}
