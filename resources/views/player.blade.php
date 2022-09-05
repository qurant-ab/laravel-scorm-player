<html>
  <head>
    <link rel="stylesheet" type="text/css" href="{{  manifest('css/scorm_player.css')  }}" />
  </head>

  <body>
    <iframe class="scorm-player"></iframe>
    <script>
     window.scorm_api_data = {{ Js::from($scorm_api_data) }};
    </script>
    <script src="{{ manifest('js/scorm_player.js') }}"></script>
  </body>
</html>
