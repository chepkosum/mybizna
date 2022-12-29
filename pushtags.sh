#!/bin/sh
# mybizna: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@
# chmod +x pushtags.sh && ./pushtags.sh

VERSION=0.9.7.5
FOLDER=$(pwd)
OLDVERSION=`cat version`

echo "
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
COPY COMMANDS BELOW
"
update_assets () {
    rm -r ../assets/src/mybizna/css
    rm -r ../assets/src/mybizna/fonts
    rm -r ../assets/src/mybizna/images
    rm -r ../assets/src/mybizna/js
    rm -r ../assets/src/mybizna/tinymce

    cp -r public/mybizna/css ../assets/src/mybizna/css
    cp -r public/mybizna/fonts ../assets/src/mybizna/fonts
    cp -r public/mybizna/images ../assets/src/mybizna/images
    cp -r public/mybizna/js ../assets/src/mybizna/js
    cp -r public/mybizna/tinymce ../assets/src/mybizna/tinymce
}

commit_assets () {
    cd ../assets

    sed -i s/$OLDVERSION/$VERSION/g  composer.json

    git add .
    git commit --allow-empty -m 'Update'
    git push origin main
    git tag $VERSION
    git push --tags

    cd ../erp
}

commit_erp_versioned () {
    sed -i s/$OLDVERSION/$VERSION/g  composer.json
    sed -i s/$OLDVERSION/$VERSION/g  Modules/*/composer.json

    git submodule foreach git add .
    git submodule foreach git commit --allow-empty -m 'Update'
    git submodule foreach git push origin main

    git submodule foreach git tag $VERSION
    git submodule foreach git push --tags

    git add .
    git commit --allow-empty -m 'Update'
    git push origin main

    git tag $VERSION
    git push --tags

    git submodule foreach gh release create $VERSION --generate-notes
    gh release create $VERSION --generate-notes
}
commit_erp () {
    git submodule foreach git add .
    git submodule foreach  git commit --allow-empty -m 'Update'
    git submodule foreach git push origin main
}

if [ $VERSION != $OLDVERSION ]
 then
    update_assets
    commit_assets
    commit_erp_versioned
else
    commit_erp
fi


echo "$VERSION" > 'version' 

