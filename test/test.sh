#!/bin/bash -x
#
# Test the SSO
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d"

#
#
(../vendor/bin/phpunit 2>&1 \
	| tee ../webroot/test-output/output.txt \
	) || true

#
# Get Transform
if [ ! -f "phpunit-report.xsl" ]
then
	wget https://cdn.openthc.com/css/phpunit-report.xsl
fi

xsltproc \
	--nomkdir \
	--output "../webroot/test-output/phpunit.html" \
	phpunit-report.xsl \
	../webroot/test-output/phpunit.xml

dt=$(date)

cat > ../webroot/test-output/index.html <<HTML
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="application-name" content="OpenTHC">
<meta name="apple-mobile-web-app-title" content="OpenTHC">
<meta name="msapplication-TileColor" content="#247420">
<meta name="theme-color" content="#247420">
<link rel="stylesheet" href="https://cdn.openthc.com/bootstrap/4.4.1/bootstrap.css" integrity="sha256-L/W5Wfqfa0sdBNIKN9cG6QA5F2qx4qICmU2VgLruv9Y=" crossorigin="anonymous">
<title>Test Result</title>
</head>
<body>
<div class="container">

<h1>Test Results ${dt}</h1>

<p>You can view the <a href="output.txt">raw script output</a>,
or the <a href="phpunit.xml">Unit Test XML</a>
which we've processed <small>(via XSL)</small> to <a href="phpunit.html">a pretty report</a>
which is also in <a href="testdox.html">testdox format</a>.
</p>

</div>
</body>
</html>
HTML
