#!/bin/bash

getPhpVersion () {
  php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;"
}

cleanup () {
  rm composer.lock || true
  rm -rf vendor || true
}

installLaravel (version) {
  composer require laravel/framework:"${version}" --dev --with-all-dependencies
}

PHP_74_LARAVEL=("^7.0", "^8.0")
PHP_80_LARAVEL=("^7.0", "^8.0", "^9.0")
PHP_81_LARAVEL=("^8.0", "^9.0", "^10.0")
PHP_82_LARAVEL=("^8.0", "^9.0", "^10.0")

if [ "$(getPhpVersion)" = "7.4" ]
then
  echo "-------- PHP 7.4 --------"
  for version in "${PHP_74_LARAVEL[@]}"
  do
    echo "### Test Laravel ${version} ###"
    cleanup
    installLaravel(version)
    composer run-script test
  done
fi

if [ "$(getPhpVersion)" = "8.0" ]
then
  echo "-------- PHP 8.0 --------"
  for version in "${PHP_80_LARAVEL[@]}"
  do
    echo "### Test Laravel ${version} ###"
    cleanup
    installLaravel(version)
    composer run-script test
  done
fi

if [ "$(getPhpVersion)" = "8.1" ]
then
  echo "-------- PHP 8.1 --------"
  for version in "${PHP_81_LARAVEL[@]}"
  do
    echo "### Test Laravel ${version} ###"
    cleanup
    installLaravel(version)
    composer run-script test
  done
fi

if [ "$(getPhpVersion)" = "8.2" ]
then
  echo "-------- PHP 8.2 --------"
  for version in "${PHP_82_LARAVEL[@]}"
  do
    echo "### Test Laravel ${version} ###"
    cleanup
    installLaravel(version)
    composer run-script test
  done
fi
