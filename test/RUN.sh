#!/bin/bash
set -euo pipefail

dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
root_dir="$dir/.."
port=12562
doc_root="$root_dir"

tmp_dir=$(mktemp -d)
function finish {
	pid=`cat $tmp_dir/server.pid`
	if [ -n "$pid" ]; then
		kill -s INT $pid
	fi
	rm -rf "$tmp_dir"
}
trap finish EXIT

cd $root_dir
nohup php -S localhost:$port -t $doc_root > test_server.log 2>&1 &
echo $! > $tmp_dir/server.pid

sleep 1

wget -O ./test/ERRORS.txt "http://localhost:$port/dl.php?channel=release&platform=linux-x86_64"
