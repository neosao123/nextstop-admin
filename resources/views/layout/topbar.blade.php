@if (Auth::guard('admin')->check())
@php
$id = Auth::guard('admin')->user()->id;
$avatar = Auth::guard('admin')->user()->avatar;
@endphp
<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">
  <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse"
    aria-expanded="false" aria-label="Toggle Navigation">
    <span class="navbar-toggle-icon">
      <span class="toggle-line"></span>
    </span>
  </button> 
  <ul class="navbar-nav navbar-nav-icons me-auto flex-row align-items-center">
    <li class="nav-item">
      <a class="nav-link"></a>
    </li>
  </ul>
  <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
    <li class="nav-item d-none">
      <div class="theme-control-toggle fa-icon-wait px-2">
        <input class="form-check-input ms-0 theme-control-toggle-input" id="themeControlToggle" type="checkbox" data-theme-control="theme" value="dark" />
        <label class="mb-0 theme-control-toggle-label theme-control-toggle-light" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Switch to light theme"><span
            class="fas fa-sun fs-0"></span></label>
        <label class="mb-0 theme-control-toggle-label theme-control-toggle-dark" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Switch to dark theme"><span
            class="fas fa-moon fs-0"></span></label>
      </div>
    </li>
    <li class="nav-item dropdown pe-2"> 
      <a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <div class="avatar avatar-xl">
          <img class="rounded-circle" src="{{  $avatar ?  asset('storage/'.$avatar) : asset('/assets/img/user/default-user.png') }}" alt="" />
        </div>
      </a>
      <div class="dropdown-menu dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
        <div class="bg-white dark__bg-1000 rounded-2 py-2">
          <a class="dropdown-item" href="{{ url('profile') }}">Profile &amp; account</a>
          <a class="dropdown-item" href="{{ url('change-password') }}">Change Password</a>
          <div class="dropdown-divider"></div>
          <!--<a class="dropdown-item" href="{{ url('settings') }}">Settings</a>-->
          <a class="dropdown-item" href="{{ route('auth.logout') }}">Logout</a>
        </div>
      </div>
    </li>
  </ul>
</nav>
@endif