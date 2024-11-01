sub vcl_error {
    set obj.http.Content-Type = "text/html; charset=utf-8";

    synthetic {"
        <?xml version="1.0" encoding="utf-8"?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html>
            <head>
                <title>"euc.repair is starting!"</title>
            </head>
            <body>
                <h1>euc.repair is starting! Check in a minute or two :)</h1>
		<p>Technical info: "} obj.status " " obj.response " " req.xid {"</p>
            </body>
        </html>
    "};
    return(deliver);
}
