# WordPress Plugin: Add First Image as Thumbnail
WordPress plugin that assigns the first image of a post as its featured image (uploads image to library)

# How it works
This plugins plugs into the 'bulk actions' menu on the "All Posts" screen. 
1. Select the posts you would like to have processed;
2. Select "Assign first image as featured" from the 'bulk actions' dropdown;
3. Click on 'Apply' to start the process.

The plugin will loop through each post and find the SRC of the first image. It will then uploaded it to the WordPress media library and assign it as the featured image of the post.

ATTENTION: If the post already has a featured image, the plugin will override it.
