Scootercam Hero Image Plugin
A WordPress plugin that displays a responsive webcam image as a hero element on your website, complete with current weather data and a link to daily timelapse videos.
What It Does
This plugin creates an attractive hero image section featuring:

Live Webcam Image - Shows the latest photo from your webcam
Current Temperature - Displays real-time temperature overlay
Humidity Level - Shows current humidity percentage
Automatic Optimization - Delivers the right image size for phones, tablets, and desktops
Video Timelapse Link - Click the image to watch today's timelapse video

Installation

Upload the plugin file to your WordPress /wp-content/plugins/ directory
Activate the plugin through the 'Plugins' menu in WordPress
That's it! The plugin is ready to use

How to Use
Add this shortcode to any page, post, or widget where you want the hero image to appear:
[scootercam-hero]
```

### Customization Options

You can customize the display with these optional settings:

**Choose Your Camera:**
```
[scootercam-hero camera="amc"]  (Amcrest camera - default)
[scootercam-hero camera="reo"]  (Reolink camera)
```

**Hide Temperature:**
```
[scootercam-hero show_temp="false"]
```

**Hide Humidity:**
```
[scootercam-hero show_humidity="false"]
```

**Custom Description for Screen Readers:**
```
[scootercam-hero alt_text="Beach view from Main Street"]
```

**Combine Multiple Options:**
```
[scootercam-hero camera="reo" show_temp="false" alt_text="Harbor view"]
What You'll See
The plugin automatically displays:

A high-quality, responsive webcam image that looks great on any device
Temperature in the top-left corner (large, easy-to-read)
Humidity percentage in the bottom-left corner
The entire image is clickable and links to today's timelapse video

Technical Notes

Images are automatically optimized for fast loading
Modern WebP format is used when supported
Falls back to JPEG for older browsers
Mobile-friendly and responsive design included
Weather data updates automatically from your system

Support
For questions or issues, visit scootercam.com
License
GPL v2 or later
