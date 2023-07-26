#/bin/bash
mkdir -p logs
date_now=$(date "+%F_%H-%M-%S")
url=''
page=1
i=0;


while [ $i -lt $page ]; do 

	json=$(curl -s 'http://localhost/api/urlFeeder/?page='$i);

	page=$(echo $json | python3 -c "import sys, json; print(json.load(sys.stdin)['sum']['pages'])")
	url=$(echo $json | python3 -c "import sys, json; print(json.load(sys.stdin)['data'])")

	url=$(echo "${url//"'"/}")
	url=$(echo "${url//","/}")
	url=$(echo "${url//"["/}")
	url=$(echo "${url//"]"/}")
	
	python3 /opt/WebBeat/WebBEAT.py -p 5 --no-whois -e http://localhost/api/v2/ -s "$url" >> logs/log$date_now.log

	i+=1
done
