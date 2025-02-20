# Digital Signage Manager

A web-frontend written in PHP/Laravel that allows end users to create playlists of digital signage content for the [Exhibit Apple TV app](https://exhibit.readthedocs.io/en/latest/).

It uses SAML2 single sign on to identify users and allow them to update content they are authorized for.

# Supported Sources

- File (PNG or MP4)
- Google Drive (Slides)
- PDF
- Text

# How to Run

The easiest way of running the application is using docker compose.  An example docker compose file is included for your reference.  It is also possible to run without Docker, directly hosting the services on a Linux machine.

### Add Google OAuth information
If you want to use the Google Slides integration, you will need to create a Google Cloud project with the Google Slides and Google Drive API services enabled.  Generate a OAuth configuration and download the oauth.json file to the root so it can be included by docker compose.

You will need to add the following scopes to your OAuth consent screen:
- auth/presentations.readonly
- auth/drive.readonly
- auth/userinfo.email

The OAuth redirect URL for web client is:
- https://signage.yourdomain.com/google/oauth_callback

### Create the configuration files
You can copy the example file `.env.example` to `.env` and update it to reflect your environment.

You should also copy `docker-compose.yaml.example` to `docker-compose.yaml` and update it to reflect your environment.

### Build the application and start it
`docker compose up -d`

### Generate a new application secret
After the app starts, regenerate the cookie secret using the following command.

`docker exec ds_app php artisan key:generate`

### Configure SSO provider for the Digital Signage SP
Once the server is running SAML SP metadata can be retrieved from `https://<your server>/saml/myidp/metadata`.

### Perform a single-sign on to the Digital Signage software
Navigate to the root of the application, or trigger IDP-initiated sign on to sign into the app.

### Promote yourself to an administrator
After logging in at least once, run the following command to promote your account to administrator status.

`docker exec ds_app php artisan make-admin <email address>`

### Create zones
You can now create zones and content.

### Scope and deploy Exhibit app to devices
Scope the Exhibit app to your Apple TV devices using your MDM.  You may want to also install a configuration profile to force them into single app mode so the Exhibit app always runs.

Below is an example AppConfig for the Exhibit app to deploy using your MDM.  This example works in Jamf Pro, and allows new Apple TVs to automatically register as they are provisioned.
```
<dict>
<key>edu.nebraska.ImageViewer.dataURL</key>
<string>https://<your server>/api/device/$SERIALNUMBER.csv</string>
<key>edu.nebraska.ImageViewer.imageTimer</key>
<integer>10</integer>
<key>edu.nebraska.ImageViewer.airplayViewHide</key>
<true/>
<key>edu.nebraska.ImageViewer.dataCheckTimer</key>
<integer>60</integer>
<key>edu.nebraska.ImageViewer.defaultBackground</key>
<string>DefaultBackgroundNoLogo</string>
<key>edu.nebraska.ImageViewer.playVideosWithAudio</key>
<true/>
<key>edu.nebraska.ImageViewer.playVideosInFull</key>
<true/>
</dict>
```

### Assign zones to devices
Once the Exhibit app is running and the device has requested content at least once, it will appear on the Devices tab.  On the Devices tab, you can assign device serial numbers to Root Zones to show different content.

# Support

This app was created by PLCS for an internal need.  It is being released to the community in the hope it may be helpful to others.  No support is offered by PLCS.