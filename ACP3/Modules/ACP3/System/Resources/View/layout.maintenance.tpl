<!DOCTYPE html>
<html lang="en">
<head>
    <title>{site_title}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="{$ROOT_DIR}ACP3/Modules/ACP3/System/Resources/Assets/css/bootstrap.min.css">
    <style>
        #maintenance {
            width: 70%;
            height: 40px;
            margin: -20px auto;
            padding: 0 20px;
            line-height: 40px;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 15%;
        }
    </style>
</head>

<body>
<div id="maintenance" class="alert alert-warning" role="alert">
    <strong>{$CONTENT}</strong>
</div>
</body>
</html>
