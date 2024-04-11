composer install --no-dev -o

mkdir crisp
cp -r config crisp
cp -r controllers crisp
cp -r js crisp
cp -r translations crisp
cp -r vendor crisp
cp -r views crisp
cp  .htaccess crisp
cp  composer.* crisp
cp  *.php crisp
cp  logo.* crisp
zip -r dist/crisp.zip crisp
zip -d crisp.zip "crisp/__MACOSX*"
zip -d crisp.zip "*/*.DS_Store"
rm -r crisp
rm -r vendor
