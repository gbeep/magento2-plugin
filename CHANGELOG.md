## 1.2.0 (October 26, 2020)

FEATURES:
* Add `Change status` mass-action in the refunds listing.  
* Add selection in the refunds listing to apply a newly added mass-action for multiple records.
* Add a link to the related order in the refunds listing. 

FIX:
* Call translate function properly in the `Helper/Data`.

## 1.1.0 (September 23, 2020)

FIX:
* Fix not displayed image that wraps link, which was uploaed via admin panel.
* Fix an exception about wrong path to he `link` template.
* Fix an error when `updatedAt` is not included in the webhook payload.
* Pass explicit variables instead of discrete `$order` to the block template.

## 1.0.0 (June 5, 2020)

Initial release