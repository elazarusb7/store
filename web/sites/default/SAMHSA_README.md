# Prebuilt dev env settings

To enable custom settings and enable services like Twig debugging, simply do the following:

* cp example.settings.local.php settings.local.php
* cp example.services.local.yml services.local.yml

If you have a customized settings.php file, merge the settings you want to keep
into settings.local.php. You will no longer need to edit settings.php as it will
be reserved for truly global settings only.

You can now freely edit both files, which are full of examples. These files will be ignored by Git. Please read through the files to understand some pretty sweet development options.

You're welcome!
