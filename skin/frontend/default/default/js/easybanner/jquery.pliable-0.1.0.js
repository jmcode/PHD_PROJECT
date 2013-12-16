(function($) {
  $.fn.ply = function() {
    return this.each(function() {
      PhotoGallery(this);
    });
  };
  
  var PhotoGallery = function(viewport, image_dir) {
    var viewport      = $(viewport);
    var previews      = viewport.find('.previews');
    var images        = viewport.find('.previews img');
    var primary_image = viewport.find('.primary');
    var preview_image = images[0];
    var image_proxy   = new Image();
    var scroll_up     = viewport.find('.scroll_up');
    var scroll_down   = viewport.find('.scroll_down');
    
    var viewport_padding = Math.ceil(viewport.width() * 0.025);
    var viewport_width   = viewport.width() - (viewport_padding * 2);
    var viewport_height  = viewport.height() - (viewport_padding * 2);
    var preview_width    = Math.ceil(viewport_width * 0.25);
    
    if(images.length == 0)
      return false;
    
    var original_image_dimensions = function(img) {
      image_proxy.setAttribute('src', $(img).attr('src'));
      return {
        'width': image_proxy.width,
        'height': image_proxy.height
      }
    };

    var image_centering_margin = function(img) {
      return Math.ceil((previews.height() - $(img).height()) / 2);
    };
    
    var resize_primary_image = function() {
      var primary_image_max_width = Math.ceil(viewport_width * 0.75);
      var primary_image_max_height = viewport_height;
      var primary_image_dimensions = original_image_dimensions(primary_image);
      var primary_image_aspect_ratio = primary_image_dimensions.width / primary_image_dimensions.height;
      var viewport_aspect_ratio = primary_image_max_width / primary_image_max_height;

      if(viewport_aspect_ratio < primary_image_aspect_ratio) {
        var height = primary_image_max_width / primary_image_aspect_ratio;
        var top = Math.ceil((primary_image_max_height - height) / 2) + viewport_padding + 'px';

        primary_image.css({
          'width': primary_image_max_width + 'px',
          'height': height + 'px',
          'top': top
        });
      } else {
        primary_image.css({
          'width': primary_image_max_height * primary_image_aspect_ratio + 'px',
          'height': primary_image_max_height + 'px',
          'top': viewport_padding + 'px'
        });
      }

      primary_image.css('right', preview_width + viewport_padding + 'px');
    };
    
    var resize_previews = function() {
      previews.css({'right':  viewport_padding + 'px',
                    'width':  preview_width + 'px',
                    'height': viewport.height()
      });

      var scroll_area_height = Math.ceil(viewport.height() * 0.07);
      var scroll_area_margin = Math.ceil(preview_width * 0.2);
      viewport.find('.scroll_up, .scroll_down').css({
        'right':  viewport_padding + scroll_area_margin + 'px',
        'width':  preview_width - (scroll_area_margin * 2) + 'px',
        'height': scroll_area_height + 'px'
      });

      previews.find('.intro, .outro').each(function() {
        $(this).css({'min-height': Math.max(image_centering_margin(preview_image), $(this).height()) + 'px'});
      });

      images.each(function() {
        var image_dimensions = original_image_dimensions(this);

        $(this).css({
          'width': preview_width + 'px',
          'height': Math.floor(preview_width * image_dimensions.height / image_dimensions.width) + 'px'
        });
      });
    };

    var image_fade_out_handler = function() {
      if(!jQuery.browser.msie)
        $(this).fadeTo(200, 0.85);
    };

    var image_fade_in_handler = function() {
      if(!jQuery.browser.msie)
        $(this).fadeTo(200, 1);
    };

    var add_vignetting = function() {
      var offset_height = 0;
      var image_src = viewport.find('.vignette_src').attr('src');
      viewport.find('.previews img, .intro, .outro').each(function() {
        $(this).after('<img class="vignette" src="' + image_src + '" />');
          var vignette = $(this).next();
          vignette.css({
            top: offset_height,
            height: $(this).height()
          });

          offset_height += $(this).height();
      });

      viewport.find('.intro, .outro').each(function() {
        if(!jQuery.browser.msie)
          $(this).next().css('opacity', 0.85);
        
        $(this).addClass('ie_not_clickable'); // IE Hack
      });

      images.each(function() {
        $(this).next().mouseenter(image_fade_out_handler)
                      .mouseleave(image_fade_in_handler);
      });
    };

    var add_previews_scrolling = function() {
      if(previews.height() < previews[0].scrollHeight) {
        viewport.find('.scroll_up, .scroll_down').show();
        
        scroll_up.click(function() {
          var prev_image = $(preview_image).prevAll('img:not(.vignette)')[0];
          if(prev_image) {
            preview_image = prev_image;
            center_preview_image();
          }
        });

        scroll_down.click(function() {
          var next_image = $(preview_image).nextAll('img:not(.vignette)')[0];
          if(next_image) {
            preview_image = next_image;
            center_preview_image();
          }
        });
      }
    };

    var center_preview_image = function(img) {
      if(img)
        preview_image = img;
      
      var previous_heights = 0;

      $(preview_image).prevAll('*:not(.vignette)').each(function() {
        previous_heights += $(this).height();
      });

      previews.animate({scrollTop: previous_heights - image_centering_margin(preview_image)});

      if($(preview_image).prevAll('img:not(.vignette)').length == 0)
        scroll_up.hide();
      else
        scroll_up.show();

      if($(preview_image).nextAll('img:not(.vignette)').length == 0)
        scroll_down.hide();
      else
        scroll_down.show();
    };

    var add_change_primary_image_handlers = function() {
      previews.find('.vignette').click(function() {
        var img = $(this).prev('img')[0];
        var new_src = $(img).attr('data-src') ? $(img).attr('data-src') : $(img).attr('src');
        if(img && new_src != primary_image.attr('src')) {
          primary_image.fadeTo(125, 0, function() {primary_image.hide().attr('src', new_src);});
          center_preview_image(img);
        }
      });
    };
    
    primary_image.load(function() {
      resize_primary_image();
      primary_image.show().fadeTo(125, 1);
    });
    
    var preload_data_src_images = function() {
      images.each(function() {
        var data_src = $(this).attr('data-src');
        if(data_src) {
          var img = new Image();
          $(img).attr('src', data_src);
        }
      });
    };
    
    
    
    viewport.find('> *').hide();
    viewport.prepend('<div class="loading">Loading Images...</div>');
    $(window).load(function() {
      // Need to load up all the images to know their widths and heights
      viewport.find('> .loading').remove();
      viewport.find('> *').show();

      previews.find('img').each(function() {
        $(this).css({'width': '100%', 'float': 'none'});
      });
      
      resize_previews();
      add_vignetting();
      add_previews_scrolling();
      center_preview_image();
      add_change_primary_image_handlers();
      $(preview_image).next('.vignette').click();
      preload_data_src_images();
    });
    
    
    // reset on page reload
    previews.scrollTop(0);
    
    
    return {
    };
  };
})(jQuery);
