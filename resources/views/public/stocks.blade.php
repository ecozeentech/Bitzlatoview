@extends('layouts.public')
@section('title', 'Stocks')
@section('content')

<div class="page-shell py-16">
  <p class="text-xs uppercase tracking-[0.2em] text-brand">Bitzlatoview</p>
  <h1 class="section-title mt-2">Stocks</h1>
  <p class="section-sub">Paper stock trading with mock instruments. Connect a licensed broker for live equities.</p>
  <div class="glass-card mt-8 p-6 text-sm text-muted">
    <p>This module is available in the authenticated app. MVP operates in paper-trading / simulation mode until licensed custody, broker, KYC, card issuing, and market-data providers are connected.</p>
    <div class="mt-6 flex flex-wrap gap-3">
      <a href="{{ route('register') }}" class="btn-brand">Get Started</a>
      <a href="{{ route('app.dashboard') }}" class="btn-outline">Open App</a>
    </div>
  </div>
</div>
@endsection
