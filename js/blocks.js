(function (blocks, element) {
  var el = element.createElement;

  blocks.registerBlockType('extend-core-taxonomies/related-posts', {
    title: 'Related Posts',
    icon: 'admin-post',
    category: 'widgets',
    edit: function () {
      return el(
        'p',
        { className: 'related-posts-block' },
        'Related Posts Widget'
      );
    },
    save: function () {
      return null; // Let the render_callback handle the rendering
    },
  });

  blocks.registerBlockType('extend-core-taxonomies/related-pages', {
    title: 'Related Pages',
    icon: 'admin-page',
    category: 'widgets',
    edit: function () {
      return el(
        'p',
        { className: 'related-pages-block' },
        'Related Pages Widget'
      );
    },
    save: function () {
      return null; // Let the render_callback handle the rendering
    },
  });
})(window.wp.blocks, window.wp.element);
