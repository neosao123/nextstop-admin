@php
  $pageTitle = 'Welcome Page';
@endphp
@extends('layout.master')

@push('styles')
  <style>
    td {
      white-space: nowrap;
    }

    .welcome-container {
      height: 70vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .greeting {
      font-size: 2rem;
      font-weight: 600;
      color: #3498db;
      margin-bottom: 10px;
      animation: fadeIn 1.2s ease-in-out;
    }

    .welcome-text {
      font-size: 1.6rem;
      font-weight: 500;
      color: #2c3e50;
      animation: fadeInUp 1.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeInUp {
      from {
        transform: translateY(20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>
@endpush

@section('content')
<div class="welcome-container">
    <div class="greeting" id="timeGreeting"></div>
    <div class="welcome-text">Welcome to {{ config('app.name') }}</div>
</div>
@endsection

@push('scripts')
<script>
  // Auto-detect and update greeting based on time
  document.addEventListener("DOMContentLoaded", function () {
      const hour = new Date().getHours();
      let greeting = "Welcome";

      if (hour >= 5 && hour < 12) {
          greeting = "Good Morning";
      } else if (hour >= 12 && hour < 17) {
          greeting = "Good Afternoon";
      } else if (hour >= 17 && hour < 21) {
          greeting = "Good Evening";
      } else {
          greeting = "Good Night";
      }

      document.getElementById("timeGreeting").textContent = greeting;
  });
</script>
@endpush
