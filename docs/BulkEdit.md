# BulkEdit

A BulkEdit field shown when selecting a bunch of posts, then selecting 'edit' from the actions dropdown, then finally
pressing the `apply` button.

Note that the bulk edit field uses the same save_posts Hook a Metabox does and usually you first have a metabox then
add a bulk edit field later.

## Parameters

- title: A name given to the bulk edit field, will also decide its id by transforming it into a slug
- postTypes: the post type where the bulk edit field should show up. Can be:
  - a string with the post type name
  - an array of post type names
  - null or ommited - will cause the bulk edit field to show up on all post types
- supporting: when given it will add all post types supporting the given feature
  `register_post_type('custom-post-type', ['supports' => ['feature'])` to the screen array
  - Note that giving a value here changes the meaning of postTypes: null(or omitted) to mean only post types that support
    the given feature. Any other postTypes value will be combined with the post_types supporting the feature.

## Plain Bulk Edit Example

```php
#[AsBulkEditContainer]
class ExampleBulkEdit
{
    public function __construct(
        private readonly ReactTemplate $template,
        private readonly Request       $request,
    )
    {
    }
    
    #[BulkEdit('Kampagne', postTypes: 'post', supporting: Plugin::CONVERSIONS_POST_TYPE_FEATURE)]
    public function bulkEdit()
    {
        return "Bulk Edit Field Content!";
    }
}
```

## Metabox with save hook and React

ExampleBulkEdit.php
```php
#[AsBulkEditContainer]
#[AsHookContainer]
class ExampleMetabox
{
    public function __construct(
        private readonly ReactTemplate $template,
        private readonly Request       $request,
    )
    {
    }
    
    #[BulkEdit('Kampagne', postTypes: 'post', supporting: Plugin::CONVERSIONS_POST_TYPE_FEATURE)]
    public function bulkEdit()
    {
        return $this->template
            ->withId('article-campaigns-bulk-edit')
            ->withInitialData([
                'campaigns' => Campaign::all()
                    ->map(fn(Campaign $campaign) => $campaign->dta()),
            ])
            ->render();
    }

    #[Hook('save_post')]
    public function savePost(int $post_id)
    {
        if ($this->request->getParam('post_type') !== 'post') {
            return;
        }

        update_post_meta($post_id, 'example-value', $this->request->getParam('example-value-from-form');
    }

}
```

example_bulk_edit.js
```javascript
import {useState} from '@wordpress/element';
import {TextControl} from '@wordpress/components';
import {reactOnReady} from 'vaf-wp-framework/reactOnReady';

function ExampleBulkEdit({exampleValue: initialExampleValue}) {
    const [exampleValue, setExampleValue] = useState(initialExampleValue);

    return (
        <div>
            <input type="hidden" name="example-value-from-form" value={exampleValue} />
            <TextControl
                label="Example Value"
                value={exampleValue}
                onChange={setExampleValue}
            />
        </div>
    );
}

reactOnReady('article-campaigns-bulk-edit', ({initialData}) => <ExampleBulkEdit {...(initialData ?? {})} />);
```

Note that the javascript file must be compiled and the result loaded for this example to work. See the example
`webpack.config.js` in the plugin skeleton for help generating wordpress react javascript files.
