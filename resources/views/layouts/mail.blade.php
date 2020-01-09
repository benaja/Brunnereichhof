<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style type="text/css">
            p{
                font-size: 1.3em;
                margin: 0;
            }

            a{
                color: #039be5;
            }

            .button {
                background-color: #26a69a;
                border-style: none;
                border-radius: 20px;
                padding: 10px 30px;
                color: white;
                font-size: 1.3rem;
                text-decoration: none;
                margin: 20px 0;
                text-transform: uppercase;
                display: inline-block;
            }

            .button a {
                color: white;
                font-size: 1.3rem;
                text-decoration: none;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0; background-color: #f4f4f4;">
       <table style="width: 90%;">
           <tr style="width: 100%;">
                <td style="width: 100%;">
                    <table align="center" width="800"  style="background-color: white; width: 800px; margin: 0 auto; padding: 20px">
                        @yield("content")
                    </table>
                </td>
           </tr>
       </table>
    </body>
    
</html>