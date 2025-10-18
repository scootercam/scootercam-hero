<?php
/**
 * Plugin Name: Scootercam Hero Image
 * Plugin URI: https://scootercam.com
 * Description: Displays webcam hero image with responsive srcset, linked to timelapse video, with weather data
 * Version: 1.0.0
 * Author: Scootercam
 * Author URI: https://scootercam.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Scootercam_Hero_Image {
    
    private $base_path = '/home/scootercam/public_html';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('scootercam-hero', array($this, 'render_hero_image'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    /**
     * Enqueue necessary styles
     */
    public function enqueue_styles() {
        // Add any custom CSS if needed
        wp_add_inline_style('w3css', '
            .flex-item-wide-np {
                position: relative;
                width: 100%;
            }
            .flex-item-wide-np img {
                width: 100%;
                height: auto;
                display: block;
            }
            .flex-item-title {
                position: absolute;
                top: 10px;
                left: 10px;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 10px 15px;
                font-size: 2em;
                font-weight: bold;
                border-radius: 5px;
            }
            .flex-item-wide-np-link {
                text-decoration: none;
                display: block;
            }
        ');
    }
    
    /**
     * Get the latest image filename from a directory
     */
    private function getLatestImage($subdirectory) {
        $image_dir = $this->base_path . '/images/' . $subdirectory . '/';
        
        if (!is_dir($image_dir)) {
            return null;
        }
        
        // Get all webp files
        $files = glob($image_dir . '*-original.webp');
        
        if (empty($files)) {
            return null;
        }
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Get the base filename without suffix
        $latest = $files[0];
        $basename = basename($latest);
        // Remove the '-original.webp' suffix to get base name
        $base_name = preg_replace('/-original\.webp$/', '', $basename);
        
        return $base_name;
    }
    
    /**
     * Generate dynamic picture element with srcset
     */
    private function generateDynamicPictureSrcset($subdirectory, $altText = '', $options = []) {
        $base_name = $this->getLatestImage($subdirectory);
        
        if (!$base_name) {
            return '<p>No images available</p>';
        }
        
        // Define the responsive sizes in order
        $sizes = [
            'mobile-sm' => 400,
            'mobile' => 480,
            'tablet-sm' => 600,
            'tablet' => 768,
            'tablet-lg' => 900,
            'desktop' => 1024,
            'desktop-lg' => 1200,
            'desktop-xl' => 1440,
            'mobile-2x' => 800,
            'desktop-2x' => 1600,
            'desktop-4k' => 1920,
            'original' => null // Will be largest
        ];
        
        $web_path = '/images/' . $subdirectory . '/';
        
        // Build WebP srcset
        $webp_srcset = [];
        $jpg_srcset = [];
        
        foreach ($sizes as $suffix => $width) {
            $webp_file = $web_path . $base_name . '-' . $suffix . '.webp';
            $jpg_file = $web_path . $base_name . '-' . $suffix . '.jpg';
            
            // Check if files exist
            $webp_path = $this->base_path . $webp_file;
            $jpg_path = $this->base_path . $jpg_file;
            
            if (file_exists($webp_path)) {
                if ($width) {
                    $webp_srcset[] = $webp_file . ' ' . $width . 'w';
                } else {
                    // For original, we need to determine actual width
                    $image_info = @getimagesize($webp_path);
                    if ($image_info) {
                        $webp_srcset[] = $webp_file . ' ' . $image_info[0] . 'w';
                    }
                }
            }
            
            if (file_exists($jpg_path)) {
                if ($width) {
                    $jpg_srcset[] = $jpg_file . ' ' . $width . 'w';
                } else {
                    $image_info = @getimagesize($jpg_path);
                    if ($image_info) {
                        $jpg_srcset[] = $jpg_file . ' ' . $image_info[0] . 'w';
                    }
                }
            }
        }
        
        if (empty($webp_srcset) && empty($jpg_srcset)) {
            return '<p>No valid images found</p>';
        }
        
        // Default sizes attribute
        $sizes_attr = '(max-width: 480px) 100vw, (max-width: 768px) 100vw, (max-width: 1024px) 100vw, 1200px';
        
        // Get options
        $loading = isset($options['loading']) ? $options['loading'] : 'lazy';
        $fetchpriority = isset($options['fetchpriority']) ? $options['fetchpriority'] : 'auto';
        $css_class = isset($options['class']) ? $options['class'] : 'hero-image';
        
        // Use the largest image as fallback
        $default_src = end($webp_srcset);
        $default_src = explode(' ', $default_src)[0]; // Get just the URL part
        
        $html = '<picture>' . "\n";
        
        if (!empty($webp_srcset)) {
            $html .= '  <source srcset="' . esc_attr(implode(', ', $webp_srcset)) . '" type="image/webp" sizes="' . esc_attr($sizes_attr) . '">' . "\n";
        }
        
        if (!empty($jpg_srcset)) {
            $html .= '  <source srcset="' . esc_attr(implode(', ', $jpg_srcset)) . '" type="image/jpeg" sizes="' . esc_attr($sizes_attr) . '">' . "\n";
        }
        
        $html .= '  <img src="' . esc_attr($default_src) . '"' . "\n";
        $html .= '       alt="' . esc_attr($altText) . '"' . "\n";
        $html .= '       loading="' . esc_attr($loading) . '"' . "\n";
        $html .= '       fetchpriority="' . esc_attr($fetchpriority) . '"' . "\n";
        $html .= '       class="' . esc_attr($css_class) . '"' . "\n";
        $html .= '       style="width: 100%; height: auto;">' . "\n";
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Get weather data from summary.json
     */
    private function getWeatherData() {
        $summary_file = $this->base_path . '/wx/summary.json';
        
        if (!file_exists($summary_file)) {
            return null;
        }
        
        $json = @file_get_contents($summary_file);
        if ($json === false) {
            return null;
        }
        
        $data = json_decode($json, true);
        return $data;
    }
    
    /**
     * Determine time of day for video naming
     */
    private function getTimeOfDay() {
        $hour = (int)date('H');
        
        if ($hour >= 0 && $hour < 9) {
            return 'night';
        } elseif ($hour >= 9 && $hour < 17) {
            return 'day';
        } else {
            return 'evening';
        }
    }
    
    /**
     * Shortcode callback function
     * Usage: [scootercam-hero camera="amc"]
     * Optional attributes:
     *   camera="amc" or "reo" (default: amc)
     *   show_temp="true/false" (default: true)
     *   show_humidity="true/false" (default: true)
     *   alt_text="Custom alt text"
     */
    public function render_hero_image($atts) {
        $atts = shortcode_atts(array(
            'camera' => 'amc',
            'show_temp' => 'true',
            'show_humidity' => 'true',
            'alt_text' => ''
        ), $atts);
        
        $camera = sanitize_text_field($atts['camera']);
        $show_temp = filter_var($atts['show_temp'], FILTER_VALIDATE_BOOLEAN);
        $show_humidity = filter_var($atts['show_humidity'], FILTER_VALIDATE_BOOLEAN);
        
        // Set defaults based on camera
        $camera_name = ($camera === 'reo') ? 'Reolink Camera' : 'Amcrest Weather Camera';
        $video_prefix = ($camera === 'reo') ? 'reolink' : 'amcrest';
        
        $alt_text = !empty($atts['alt_text']) ? sanitize_text_field($atts['alt_text']) : $camera_name;
        
        // Get weather data
        $weather = $this->getWeatherData();
        $beach_temp = 0;
        $beach_humidity = 0;
        
        if ($weather && isset($weather['local']['beach'])) {
            $beach_temp = $weather['local']['beach']['temp_f'] ?? 0;
            $beach_humidity = $weather['local']['beach']['humidity'] ?? 0;
        }
        
        // Generate timelapse video URL
        $time_of_day = $this->getTimeOfDay();
        $video_url = '/timelapse/videos/' . $video_prefix . '_' . $time_of_day . '_' . date('Ymd') . '.mp4';
        
        // Generate the hero image HTML
        $picture_html = $this->generateDynamicPictureSrcset($camera, $alt_text, [
            'loading' => 'eager',
            'priority_image' => true,
            'fetchpriority' => 'high',
            'class' => 'hero-image'
        ]);
        
        // Build the complete HTML
        $html = '<a href="' . esc_url($video_url) . '" class="flex-item-wide-np-link">' . "\n";
        $html .= '    <div class="flex-item-wide-np">' . "\n";
        $html .= '        ' . $picture_html . "\n";
        
        if ($show_temp) {
            $html .= '        <div class="flex-item-title">' . round($beach_temp) . '&deg;</div>' . "\n";
        }
        
        if ($show_humidity) {
            $html .= '        <div style="position: absolute; bottom: 10px; left: 10px; background: rgba(0, 0, 0, 0.7); color: white; padding: 5px 10px; border-radius: 5px;">' . "\n";
            $html .= '            Humidity ' . round($beach_humidity) . '%' . "\n";
            $html .= '        </div>' . "\n";
        }
        
        $html .= '    </div>' . "\n";
        $html .= '</a>';
        
        return $html;
    }
}

// Initialize the plugin
new Scootercam_Hero_Image();