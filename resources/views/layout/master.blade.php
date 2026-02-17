<!DOCTYPE html>

<html lang="en">

<head>
  @include('layout.header')
</head>

<body>
  <main class="main" id="top">
    <div class="container-fluid" data-layout="container">
      @include('layout.sidebar')
      <div class="content">
        @include('layout.topbar')
        @yield('content')
        @include('layout.footer')
      </div>
    </div>
  </main>
  @include('layout.bottombar')
</body>

</html>
