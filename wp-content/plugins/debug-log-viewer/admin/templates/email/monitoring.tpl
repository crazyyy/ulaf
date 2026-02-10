<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"
    style='font-family:"Poppins", sans-serif; font-size:14px; line-height:1.5; margin:0; padding:0'>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Debug Log Viewer Report</title>
    <style>
        tr:nth-child(even) {
            background-color: #F0F0F0
        }
    </style>
</head>

<body style='font-family:"Poppins", Arial, Helvetica, sans-serif; font-size:14px; line-height:1.5; margin:0; padding:0'>
    <!-- Main table structure -->
    <table style="border-collapse:collapse; border-radius:4px; font-size:14px; margin-top:10px; overflow:hidden; width:100%">
        <tr>
            <td style="border-bottom:1px solid #ddd; padding:10px">
                <div class="wrapper" style="width:100%" width="100%">
                    <div class="container" style="margin:0 auto; max-width:600px; width:100%" width="100%">
                        <header
                            style="background:linear-gradient(to bottom right, #3646FF, #A352FF); color:#fff; padding:20px 32px">
                            <div class="logo" style="float:left; margin-top:12px">
                                <img src="http://www.lysyiweb.net/wp-content/uploads/2025/04/logo-2.png" alt="Logo">
                            </div>
                            <div class="text" style="margin-left:100px">
                                <h2 style="font-size:20px; font-weight:600; margin-bottom:5px; margin-top:0">Debug Log
                                    Viewer</h2>
                                <span style="font-size:14px">Monitoring reports about issues found on the website <a
                                        style="color: #fff" href="{{dbg_lv_website}}">{{dbg_lv_website}}</a></span>
                            </div>
                            <div style="clear:both;"></div>
                        </header>
                        <main style="background-color:#fff; color:#000; padding:20px 32px">
                            <p style="margin-bottom:5px;">Alert generated at {{dbg_lv_generated_date}}
                            </p>
                            <p style="margin-bottom:5px; margin-top:0">See the details of these scan results on your
                                site at the plugin's page</p>

                            <table
                                style="border-collapse:collapse; border-radius:4px; font-size:14px; margin-top:10px; overflow:hidden; width:100%">
                                <thead>
                                    <tr>
                                        <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">#</th>
                                        <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">Type</th>
                                        <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">Description</th>
                                        <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">File</th>
                                         <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">Line</th>
                                        <th style="background-color:#F0F0F0; font-weight:normal; padding:10px; text-align:left; border-bottom:1px solid #ddd">Hits</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{dbg_lv_summary}}
                                </tbody>
                            </table>

                            <div class="summary" style="margin-top:20px; text-align:center">
                                <p style="margin-bottom:5px; margin-top:0">Pay attention to the problems to ensure the
                                    best experience for visitors to your
                                    website.</p>
                                <p style="margin-bottom:5px; margin-top:0">Thank you for using the Debug Log Viewer!</p>
                            </div>
                        </main>
                        <footer
                            style="background:linear-gradient(to bottom right, #3646FF, #A352FF); color:#fff; padding:15px 32px; text-align:center">
                            <h3 style="font-size:16px; font-weight:600; margin-bottom:10px">Have any questions?</h3>
                            <p style="margin:5px 0">Write us to <a href="mailto:sanchoclo@gmail.com"
                                    style="color:#fff; text-decoration:underline">sanchoclo@gmail.com</a></p>
                            <p style="margin-top:5px;">To stop receiving this Emails disable notification on the plugin
                                page</p>
                        </footer>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
