=== Next Available ===
Contributors: victoriagrif7
Tags: business-tools, google-calendar
Requires at least: 4.9.1
Tested up to: 6.7.1
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your next available date from your Google Calendar. Perfect for freelancers.

== Documentation ==

- [Documentation](https://buddyclients.com/docs/)
- [User Guides](https://buddyclients.com/help/)
- [Roadmap](https://buddyclients.com/roadmap/)
- [Source Code on GitHub](https://github.com/SmokiesScribe/buddyclients-free)
- [Terms of Service](https://buddyclients.com/buddyclients-free-terms/)

== External Services ==

The Next Available plugin connects to external services to display the most up to date information based on your calendars.

### Google Calendar

If enabled, the Google Calendar integration is used to retrieve your most up to date calendar information. When the Google Calendar integration is active:

- The user’s IP address and browser information may be transmitted to Google as part of the OAuth authentication process.
- Google may set cookies or track user interactions as outlined in their [Privacy Policy](https://policies.google.com/privacy) and [Terms of Service](https://policies.google.com/terms).
- The plugin only accesses calendar data that the user explicitly grants permission to through the Google OAuth consent screen.
- Users can disconnect their Google account at any time, which revokes access and deletes stored tokens.

### Proxy Server (Optional)

This plugin uses a separate proxy server at https://buddyclients.com to securely manage Google OAuth token exchanges and refreshes. The proxy server:

- Handles all sensitive operations involving the Google client ID and client secret to prevent exposure of these credentials in the plugin or user browsers.
- Acts as an intermediary between your WordPress site and Google’s OAuth servers, ensuring secure token management.
- Does not store or log any user personal data beyond what is necessary to facilitate authentication and token refresh.
- Helps comply with security best practices by isolating sensitive credentials away from the plugin codebase.
- For more information, see our [Privacy Policy](https://buddyclients.com/nextav-privacy) and [Terms of Service](https://buddyclients.com/terms).

Advanced users can choose to opt out of using the default proxy server and instead configure their own Google API credentials directly within the plugin settings.

== Requirements ==

To run Next Available, we recommend your host supports:

* PHP version 7.2 or greater.
* MySQL version 5.6 or greater, or, MariaDB version 10.0 or greater.
* HTTPS support.

== Installation ==

1. Navigate to Plugins > Add New in your WordPress admin dashboard.
2. In the search bar, type Next Available and press Enter.
3. Locate the Next Available plugin in the search results and click the Install Now button.
4. Once the installation is complete, click Activate to enable the Next Available plugin on your site.
5. After activation, go to the Next Available settings to configure the plugin as needed.

== Screenshots ==

1. Screenshot 1: DESCRIPTION
   Screenshot URL: /assets/media/screenshots/screenshot-2.png
   
2. Screenshot 2: DESCRIPTION
   Screenshot URL: /assets/media/screenshots/screenshot-4.png


3. Screenshot 3: DESCRIPTION
   Screenshot URL: /assets/media/screenshots/screenshot-5.png

== Banner ==

The banner image used for the plugin page:
Banner URL: /assets/media/banner-772x250/banner.png

== Changelog ==

TODO: Create full calendar.

= 1.0.0 - June 17, 2025 =
* Initial Release


                                @@@%@@#                            
                             @@@@@@@@                              
                           @@@@@@@@                                
                        @@@@@@@@@@                                 
                       @@@@@@@@@@                                  
                      @@@@@@@@@@@                                  
                     @@@@@@@@@@@                                   
                    @@@@@@#*#@@                                    
                    @@@@@@##%@@                                    
               @@  @@@@@@@@%%@@                                    
               @@@ @@@%%@@@@@@@                  @%*#@@            
              %#%@ @@@%%%@@@@@@             @@@@@@@@@@@@           
              @@@@@@@@@*=#%@@%%        @@@@@@@@@@@@@@@@            
              @%###%@@@*=+#%@%*     @@@@@@@@@%#%@@@@@              
              @@%#*#%%@%#*%@@%+       @@@@@@@%%#@@@@               
              @@@@%@@@@@##%%%%#%      @@@@%%@@%#=*%*               
              @@@%%@@@@%%%%@@%@@@     %#*%%#%%@#==#%               
              @%#+*#*=-++=*%%%%##=    %#**#+*#%%###@               
               %#***=--+++#%#%#---+   @%#%%*+**#%%%%#              
                ***+--+++==+#%%%##%@  ###++*#+*@%#%%*+             
                 ##*==+--==+*#%%%%%###=++===*##%##@%*+*            
                   %%%@+=++*+-:-##=++==++=-:=**%%#%%****           
                --    #=-++++-:=#**#*==++---+###%*+***++           
                 *#%#*-:-===++=+#*==+*%%*---==-+*+*##+::           
                  %%%#=--+#%%+:=*+*###%%*-=+++***++*#+=+           
                   %%#=::+#%#+-+##%%%####+**####*++#%#**           
                     **++**#*--=**#%%#*#%#*+*##***###%#            
                        #*##*==+++#######%%%######%#%%%            
        @%%**%@              ###+=+#*#%#%##%%#**#%%#%              
      %%%%%**%@%#              %#+**+*#%%##%%*+*%#*##              
    %##%%%%  @%##%@@@            +*###%%%#%%%#+*%%%%%@             
   @@#*#       @@@@@@@                     %%%%%%%%%##             
   @@%#        %%@@@@@@         %#%%%@%%%@  %%#*##%%@%#            
   @@@@         @@@@@@@@@    ####%%%##%@@%%% #*+*%%%@%#            
   @@@@@           @@@@   @@%%%%%#@%##%@@@%%@ ++#%%@@%%%           
   @%%@@@              @#%%@@@@%%@@%%%@@@%%%@  %%%%@@@%%%          
    %%%%@@@         %%%@%%@@@@%%%@@@@@@@@@@@@   @%#%@@@@@          
     @%%@@@@@@%%%%%@##%@@@@@%%%%@@@@@@@@@@@@     %%%@@@@@@         
       @@@@@@@%%#%%@%#%@@@@%%@@@@@@@@@@@@%        @@@@@@@@         
           @@@@%%@@@ %@@@@@@@@@@@@@@@@@@@@@@@@@    @@@@@%##@@      
                    @@@@@@@@@@@@@@@@@@%@@@@@@@@@@   @@@@##%@@@@    
                   @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@  @@@@@@@@@@@    
                    @@@@@@@@@@@@@@@@@@@@@@@@@@@@@     @@@@@@@@