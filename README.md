# NaViTo
NaViTo is a tool to create simple web-based virtual tours.
Each tour consists of several picture pages that can be navigated with mouse clicks.
On each page individual areas (region of interest, ROI) can be defined, which show further information in a popup when clicked.
This popup can contain a description text, an image and an audio track.

## Configuration
All contents are defined in a file `pages.txt` that must be located in the same folder as `generate.php`.
NaViTo expects the configuration file in a [YAML](https://en.wikipedia.org/wiki/YAML) syntax.

Following parameters needs to be set (default values in brackets, placeholders in square brackets):
- settings:
  - output_folder: folder where the .html files are written into ("www/")
  - image_path: base URL for the image files ("images/")
  - audio_path: base URL for the audio files ("audio/")
- common:
  - title: Website title
  - home: URL of the home page ("/")
  - imprint: URL to an imprint of the website ("/")
  - privacy: URL to the privacy statement of the website ("/")

In addition to these global configuration parameters, each page has to be defined as a child of the root "pages", e.g.:
- pages:
  - [page_name]:
    - title: title of the page, e.g. "Page 1"
    - image: background image, e.g. "page_name.jpeg"
    - width: width of the background image
    - height: height of the background image
    - info:
      - [ROI_name]:
        - left: left boundary of the ROI (0)
        - top: top boundary of the ROI (0)
        - width: width of the ROI (100)
        - height: height of the ROI (100)
        - title: Title as displayed as the header of the ROI
        - description: Text (HTML allowed) as shown in the popup as a description of the ROI
        - image: Optional. Filename of an image-file that is displayed in the popup
        - audio: Optional. Filename of a .mp3-file that can be played in the popup
      - [ROI_name_2]:
        - ... (see above)
    - navigation:
      - [navigation_name]:
        - url: URL of the linked page, e.g., "page2.html"
        - type: image/SVG-File to show for this navigation, uses the specified file from template/svg, e.g., "pfeil_links"
      - [navigation_name_2]:
        - ... (see above)

All childs of `info` declare a region of interest, that shows a popup on a click event. You can specifiy none or as much childs/ROIs as you like. However, each child has to use a separate `ROI_name`.
Parameters `image` and `audio` are optional.

The object `navigation` contains all clickable URLs to other pages to navigate through the tour. As for `info` the number of navigation childs is unlimited and each entry has to be unique (i.e. no duplicates of `navigation_name`).
Currently, most of the SVG-Files (or snippets) do not offer parameters for configuration. The coordinates are hard coded and they were optimized for image sizes of 1500x1000 pixels.
Only the type "area_link", a rectangular clickable navigation region, accepts the parameters `left`, `top`, `width` and `height`.

## Generation of the pages
Running `generate.php` once will generate all HTML pages as defined in `pages.txt`. The output will be stored in your specified output folder (default: www). The generated pages are static from a server perspective, i.e., PHP is only needed once for the generation of the pages. The folder "www" contains all needed files.
I strongly advise to move the file `generate.php` to a location outside of your web folder after successfull generation of the pages to reduce security risks.






