mkdir crisp
cp -r views crisp
cp  *.xml crisp
cp  *.php crisp
cp  logo.* crisp
cp  *.md crisp
zip -r crisp crisp
zip -d crisp.zip "crisp/__MACOSX*"
zip -d crisp.zip "*/*.DS_Store"
rm -r crisp
