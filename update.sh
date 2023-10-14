wget http://www.vpmt.ru/docs/rasp2.doc -O /tmp/schedule.doc
unoconv -d document --format=docx /tmp/schedule.doc
php /home/sysadmin/Texbot-php/schedule_parser.php