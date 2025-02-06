# QuickEdit

A QuickEdit field is shown when selecting pressing 'quick edit' on a post in the post list

Note that the quick edit field uses the same save_posts Hook a Metabox does and usually you first have a metabox then
add a quick edit field later.

Also note that the quick edit form field is rendered only once for the whole table and is moved around when you click on


## Parameters

- title: A name given to the quick edit field, will also decide its id by transforming it into a slug suffixed with '-quick'
- postTypes: the post type where the quick edit field should show up. Can be:
  - a string with the post type name
  - an array of post type names
  - null or ommited - will cause the quick edit field to show up on all post types
- supporting: when given it will add all post types supporting the given feature
  `register_post_type('custom-post-type', ['supports' => ['feature'])` to the screen array
  - Note that giving a value here changes the meaning of postTypes: null(or omitted) to mean only post types that support
    the given feature. Any other postTypes value will be combined with the post_types supporting the feature.

## Plain Quick Edit Example

```php
#[AsQuickEditContainer]
class ExampleQuickEdit
{
    public function __construct(
        private readonly ReactTemplate $template,
        private readonly Request       $request,
    )
    {
    }
    
    #[QuickEdit('Kampagne', postTypes: 'post', supporting: Plugin::CONVERSIONS_POST_TYPE_FEATURE)]
    public function quickEdit()
    {
        return QuickEditField::fromFormFieldData(
            formField: fn(RenderQuickEditFormFieldEvent $e) => $this->template
                ->withId('my-quick-edit-field')
                ->withInitialData([
                    'name' => $e->columnName,
                ])
                ->render(),
            data: fn(QuickEditPostDataEvent $e) => [
                'post_data' => get_post($e->postId),
            ]
        );
    }
}
```

## QuickEdit with save hook and React

ExampleQuickEdit.php
```php
#[AsQuickEditContainer]
#[AsHookContainer]
class ExampleMetabox
{
    public function __construct(
        private readonly ReactTemplate $template,
        private readonly Request       $request,
    )
    {
    }
    
    #[QuickEdit('Kampagne', postTypes: 'post', supporting: Plugin::CONVERSIONS_POST_TYPE_FEATURE)]
    public function quickEdit()
    {
        return QuickEditField::fromFormFieldData(
            formField: fn(RenderQuickEditFormFieldEvent $e) => $this->template
                ->withId('my-quick-edit-field')
                ->withInitialData([
                    'name' => $e->columnName,
                ])
                ->render(),
            data: fn(QuickEditPostDataEvent $e) => [
                'post_data' => get_post($e->postId),
            ]
        );
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

example_quick_edit.js
```javascript
import {useState, useEffect} from '@wordpress/element';
import {TextControl} from '@wordpress/components';
import {reactOnQuickEdit} from 'vaf-wp-framework/reactOnReady';
import {QuickEdit} from 'vaf-wp-framework/QuickEdit';

function ExampleQuickEdit({postId, name}) {
    const [exampleValue, setExampleValue] = useState(null);
    
    useEffect(() => {
      setExampleValue(
              QuickEdit.fromPostIdFieldName(postId, name).initialValue
      );
    }, [])
  
    if(exampleValue === null) {
      return null;
    }

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

reactOnQuickEdit('my-quick-edit-field', ({postId, initialData}) => <ExampleQuickEdit
        {...(initialData ?? {})}
        postId={postId}
/>);
```

Note that the javascript file must be compiled and the result loaded for this example to work. See the example
`webpack.config.js` in the plugin skeleton for help generating wordpress react javascript files.
