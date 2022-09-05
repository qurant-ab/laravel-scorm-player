<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sco->title }}</title>
    <style>
     * {
       margin: 0;
       padding: 0;
     }

     html,
     body {
       height: 100%;
     }

     iframe.scorm-player {
       position: absolute;
       width: 100%;
       height: 100%;
       border: none;
     }
    </style>
  </head>

  <body>
    <iframe class="scorm-player"></iframe>
    <script>
     window.scorm_api_data = {{ Js::from($scorm_api_data) }};
    </script>
    <script src="{{ manifest('js/scorm_player.js') }}"></script>
  </body>
</html>
