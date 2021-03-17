cd public_html
git pull origin master
grunt
cd scripts
php update_version_asset.php
cd ..
git add application/helpers/utility_helper.php
git commit -m "update asset version"
git push
