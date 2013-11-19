window.onload = function() {

  function loadUI() {
    onServer.page('public/landing', '#template');
  }

  setTimeout(loadUI, 2000);
}