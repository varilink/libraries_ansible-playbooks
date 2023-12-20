<?php
wp_load_translations_early();

$protocol = wp_get_server_protocol();
header( "$protocol 503 Service Unavailable", TRUE, 503 );
header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
    <head>
        <title>{{ domain_organisation }} - Site Maintenance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    </head>
    <body>
        <div class="container text-center mt-5">
            <h1>Site Maintenance</h1>
            <p>This site is down for maintenance. We expect to have it back up again soon, so please come back in a little while.</p>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    </body>
</html>