<nav class="navbar navbar-light navbar-vertical navbar-expand-xl navbar-vibrant">
  <div class="d-flex align-items-center">
    <div class="toggle-icon-wrapper">
      <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Toggle Navigation">
        <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
      </button>
    </div>
    <a class="navbar-brand" href="{{ url('/') }}">
      <div class="d-flex align-items-center py-3">
        <img class="me-2" src="{{ asset('assets/img/location_logo.png') }}" alt="carrier-logo" height="30" />
        <span class="hero-title" >Next Stop</span>
      </div>
    </a>
  </div>
  <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
    <div class="navbar-vertical-content scrollbar">
      <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
        @include('layout.sidebar.main')
      </ul>
    </div>
  </div>
</nav>
