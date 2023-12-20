# Metabox

A metabox is shown on the edit screen of a post, page or custom post type. It is usually coupled with a save hook to
save the data inputted in the metabox.

## Parameters

- title: the heading shown in the metabox
- id: set a specific id for the metabox if you need to refer to it elsewhere. You usually want to leave this empty which
  will generate the id `strtolower(${classname without path}_${methodname})`
- screen: the post type where the metabox should show up. Can be:
  - a string with the post type name
  - an array of post type names
  - null or ommited - will cause the metabox to show up on all post types
- supporting: when given it will add all post types supporting the given feature
  `register_post_type('custom-post-type', ['supports' => ['feature'])` to the screen array
  - Note that giving a value here changes the meaning of screen: null(or omitted) to mean only post types that support
    the given feature. Any other screen value will be combined with the post_types supporting the feature.
- context: the context where the metabox should show up. Can be one of the following:
    - Metabox::CONTEXT_NORMAL - bellow the content editor
    - Metabox::CONTEXT_SIDE - in the sidebar
    - Metabox::CONTEXT_ADVANCED - in advanced section of the sidebar
- priority: The priority, determines where in the list of metaboxes this metabox will appear. Can be one of the following:
    - Metabox::PRIORITY_HIGH
    - Metabox::PRIORITY_CORE
    - Metabox::PRIORITY_DEFAULT
    - Metabox::PRIORITY_LOW

## Plain Metabox Example

```php

#[AsMetaboxContainer]
class ExampleMetabox
{
    #[Metabox(title: 'Heading', screen: 'post', context: Metabox::CONTEXT_SIDE)]
    public function anyFunctionName()
    {
        echo "Metabox Content!";
    }
}
```

## Metabox with save hook and React

ExampleMetabox.php
```php
#[AsMetaboxContainer]
#[AsHookContainer]
class ExampleMetabox
{
    public function __construct(
        private readonly ReactTemplate $metaboxTemplate,
        private readonly Request       $request,
    )
    {
    }

    #[Metabox(title: 'Example Metabox', screen: 'post', context: Metabox::CONTEXT_SIDE)]
    public function myExampleMetabox()
    {
        $this->metaboxTemplate
            ->withId('example-metabox')
            ->withInitialData([
                'exampleValue' => get_post_meta(get_post()->ID, 'example-value', true)
            ])
            ->output();
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

example_metabox.js
```javascript
import {useState} from '@wordpress/element';
import {TextControl} from '@wordpress/components';
import {reactOnReady} from 'vaf-wp-framework/reactOnReady';

function ExampleMetabox({exampleValue: initialExampleValue}) {
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

reactOnReady('article-campaigns-metabox', ({initialData}) => <ExampleMetabox {...(initialData ?? {})} />);
```

Note that the javascript file must be compiled and the result loaded for this example to work. See the example
`webpack.config.js` in the plugin skeleton for help generating wordpress react javascript files.
