# Google Maps API Setup

This application uses Google Maps to display car locations and enable nearby car searches.

## Getting Your Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - **Maps JavaScript API** (for displaying maps)
   - **Geocoding API** (optional, for address to coordinates conversion)
4. Go to "Credentials" and create an API key
5. Restrict your API key (recommended):
   - Application restrictions: HTTP referrers
   - Add your domain (e.g., `localhost/*` for development, `yourdomain.com/*` for production)
   - API restrictions: Select "Maps JavaScript API" and "Geocoding API"

## Configuration

1. Open `assets/js/map.js`
2. Find this line:
   ```javascript
   const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY';
   ```
3. Replace `'YOUR_GOOGLE_MAPS_API_KEY'` with your actual API key:
   ```javascript
   const GOOGLE_MAPS_API_KEY = 'AIzaSyYourActualAPIKeyHere';
   ```

## Free Tier Limits

Google Maps offers a free tier with:
- $200 free credit per month
- Maps JavaScript API: Free for first 28,000 loads per month
- Geocoding API: Free for first 40,000 requests per month

For most small to medium websites, this is sufficient.

## Fallback

If no API key is configured, the application will automatically fall back to OpenStreetMap (free, no API key required).

## Security Note

**Never commit your API key to version control!**

Consider:
- Using environment variables
- Storing the key in a separate config file (not in git)
- Using server-side configuration






