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

If you've installed the plugin previously, look for it in the list and Deactivate and Delete it.

Click "Upload Plugin" on the next screen and select the zip file created in the previous step.

Click "Install Now" to install the plugin, and then click "Activate Plugin" on the next screen.

Navigate to the Settings/General and make sure the API key is set.
