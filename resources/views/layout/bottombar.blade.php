<script src="{{ asset('assets/vendors/popper/popper.min.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/vendors/anchorjs/anchor.min.js') }}"></script>
<script src="{{ asset('assets/vendors/fontawesome/all.min.js') }}"></script>
<script src="{{ asset('assets/vendors/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/jquery-confirm.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script type="text/javascript" src="{{ asset('site-plugins/common.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
  const currentUrl = window.location.href;

  // Select all sidebar navigation links
  document.querySelectorAll('.nav-item .nav-link').forEach(link => {
    // Check if the link's href matches the current URL to set the active state
    if (link.href === currentUrl) {
      link.classList.add('active');

      // Open any parent dropdown for the active link
      const parentCollapse = link.closest('.collapse');
      if (parentCollapse) {
        const parentNavLink = document.querySelector(`a[href="#${parentCollapse.id}"]`);
        if (parentNavLink) {
          parentNavLink.classList.add('active');
          parentCollapse.classList.add('show');
        }
      }
    }

    // Add click event listener to update active class on click
    link.addEventListener('click', function() {
      document.querySelectorAll('.nav-item .nav-link').forEach(item => {
        item.classList.remove('active');
      });
      this.classList.add('active');

      // Ensure dropdown remains open on click
      const parentCollapse = this.closest('.collapse');
      if (parentCollapse) {
        const parentNavLink = document.querySelector(`a[href="#${parentCollapse.id}"]`);
        if (parentNavLink) {
          parentNavLink.classList.add('active');
          parentCollapse.classList.add('show');
        }
      }
    });
  });
});

</script>
@if (session('success'))
  <script>
    Toastify({
      text: "{{ session('success') }}",
      duration: 2000,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
      close: false,
      style: {
        background: "linear-gradient(to right, #00b09b, #96c93d)",
      },
      onClick: function() {}
    }).showToast();
  </script>
@endif
@if (session('error'))
  <script>
    Toastify({
      text: "{{ session('error') }}",
      duration: 2000,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
      close: false,
      style: {
        background: "linear-gradient(to right, #ff0000, #ff0000)",
      },
      onClick: function() {}
    }).showToast();
  </script>
@endif

@if (session('step-two'))
  <script>
    Toastify({
      text: "Step-two Complete",
      duration: 2000,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
      close: false,
      style: {
        background: "linear-gradient(to right, #00b09b, #96c93d)",
      },
      //onClick: function() {window.location.href = "{{ url('payment') }}";},
      callback: function() {
        //window.location.href = "{{ url('payment') }}";
        window.location.href = "{{ url('stepthree') }}";
      }
    }).showToast();
  </script>
@endif

@if (session('step-three'))
  <script>
    Toastify({
      text: "Step-three Complete",
      duration: 2000,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
      close: false,
      style: {
        background: "linear-gradient(to right, #00b09b, #96c93d)",
      },
      //onClick: function() {window.location.href = "{{ url('payment') }}";},
      callback: function() {
        window.location.href = "{{ url('payment') }}";
      }
    }).showToast();
  </script>
@endif
<script>
  function ValidateAlpha(evt) {
    var charCode = (evt.which) ? evt.which : window.event.keyCode;
    if (charCode <= 13) {
      return true;
    } else {
      var keyChar = String.fromCharCode(charCode);
      var re = /^[a-zA-Z ]+$/;
      return re.test(keyChar);
    }
  }
</script>

@stack('scripts')
