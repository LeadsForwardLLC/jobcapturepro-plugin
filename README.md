=== JobCapturePro WordPress Plugin ===

Use JobCapturePro to capture job leads and manage your business.

== Installation ==

In your terminal, navigate to the parent directory from here:

`$ cd ../`

If there is an existing zip file from a previous install, you can remove it:

`$ rm jobcapturepro-plugin.zip`

or if there are no other zip files you want to keep in the parent directory, you can use this shorthand:

`$ rm *.zip`

Create the archive for distribution, being sure to exclude git version control metadata:

`$zip -r jobcapturepro-plugin.zip jobcapturepro-plugin -x '*.git*'`

In WordPress, navigate to the Plugins menu and click "Add New Plugin" at the top of the screen.

In most cases, there should be no issue if you have an active installation of the plugin. If desired, look for the plugin in the list of installed plugins, Deactivate, and Delete it.

Click "Upload Plugin" on the next screen and select the zip file created in the previous step.

Click "Install Now" to install the plugin if this is the first time installing it, and then click "Activate Plugin" on the next screen. Otherwise, choose to replace the existing plugin and it will be updated automatically.

Navigate to the Settings/General and make sure the API key is set.
