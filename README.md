# e2xtreampicons
PHP Script to download channel icons from xtream codes playlists and upload them as picons to enigma2 receiver via ftp. This is helpful if you have created iptv bouquets with plugins like jedimakerxtream.

The picons are getting cropped and sized correctly to xpicon-size.

![screenshot](screenshot.png)

# Requirements
- FTP server enabled on e2 receiver
- OpenWebif enabled on e2 receiver (*API reachable **without** authentication!*)

You can test your api by opening _http://IP-OF-YOUR-BOX/api/about_ in your browser

# Usage
You have to run this script in your local network, e.g. on an apache2 webserver.

I recommend using an [XAMPP](https://www.apachefriends.org/de/index.html)-system on Windows, cause the php-gdlib image library has a transparency bug in some linux distributions that will lead to wired backgrounds on the picon-images.

If you do use xampp, just copy the php files to the htdocs folder and edit the config.php. You have to enter:

`$ftp_server = ""; //IP of e2 Receiver`

`$ftp_user = ""; // FTP User on e2 receiver`

`$ftp_pass = ""; // FTP password`

`$user = ""; // xtream codes username`

`$pass = ""; // xtream codes password`

`$dns = "http://providerdns:providerport"; // xtream codes provider url`

After that you should be able to open the webinterface in your browser via *localhost*.

The header tells you if your FTP and XTREAM connection was successfull.

If all went well, you should see your live categories in the list. Your Userbouquets from receiver are pre-selected (this only works if you dont have a prefix set in jedimakerxtream; the bouqeuts must be named identically). Of course you can select the categories manually (multiple with CTRL+leftclick or SHIFT+leftclick).

Check the box, if you want to upload the picons directly to your e2 box. Otherwise, picons will be created in subfolder /picon (you have to create that first) of your htdocs folder.

Submit the page by hitting *Generate Picons*. Page should reload with an output-container at the end. Depending on amount of channels, the creation will take some time. As long as the page loading-indicator is busy, the process is ongoing. Wait till you get the *Finished* message at the end of the page.

Reading the output you can find errors like dead urls for picons in your providers playlist.

## Important ##
This script is just put together to just work. It is uncommented and not optimized. **It is very likely, that you have to change things to get it to work with your provider! I did only test it with my provider.**

