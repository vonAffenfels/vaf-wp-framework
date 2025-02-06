# CustomColumn

A CustomColumn is a column which can be shown while looking at a post type post list

## Parameters

- title: A name given to the custom column field, will also decide its id by transforming it into a slug suffixed with '-custom'
- postTypes: the post type where the quick edit field should show up. Can be:
  - a string with the post type name
  - an array of post type names
  - null or ommited - will cause the quick edit field to show up on all post types
- supporting: when given it will add all post types supporting the given feature
  `register_post_type('custom-post-type', ['supports' => ['feature'])` to the screen array
  - Note that giving a value here changes the meaning of postTypes: null(or omitted) to mean only post types that support
    the given feature. Any other postTypes value will be combined with the post_types supporting the feature.

## Plain Custom Column Example

```php
#[AsCustomColumnContainer]
class ExampleCustomColumn
{
    public function __construct(
        private readonly ReactTemplate $template,
        private readonly Request       $request,
    )
    {
    }
    
    #[CustomColumn('My Column', postTypes: 'post', supporting: Plugin::CONVERSIONS_POST_TYPE_FEATURE)]
    public function customColumn(int $postId)
    {
        return get_post($postId)->post_title;
    }
}
```
