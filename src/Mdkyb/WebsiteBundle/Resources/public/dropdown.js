$(function(){
    var dropdowns = $('#nav > ul > li > ul');

    $('#nav').mouseleave(function(){
        dropdowns.hide();
    });

    $('#nav > ul > li').each(function(){
        var section = $(this);
        var dropdown = section.find('ul');

        section.mouseenter(function(){
            dropdowns.hide();
            dropdown.show();
        });
    });

    $('textarea').click(function(){
        $('textarea').animate({height: '400px'});
    });

    $('textarea').keyup(function(){
        var preview = $('.preview');
        var text = $('textarea').val();
        var avatar = $('img.avatar');

        var imgcode = '';
        if (avatar.length > 0 ) {
            var imgcode = '<img class="avatar" src="' + avatar.attr('src') + '" alt="" />';
        }

        html = text;

        html = html.replace('>', '&gt;');
        html = html.replace('>', '&lt;');

        html = html.replace(/\n\*(.*)/g, '<ul><li>$1</li></ul>');
        html = html.replace(/<\/ul>[ \n]*<ul>/g, '');

        html = html.replace(/\*([^\n]*?)\*/g, '<em>$1</em>');

        html = html.replace(/(.*)\n+(-{5,})/g, '<h3>$1</h3>');

        html = html.replace(/http:\/\/([^ \n]+)/g, '<a href="http://$1">http://$1</a>');
        html = html.replace(/\[([^\n]+?)\][ \n]*<a href="(.*?)">(.*?)<\/a>/g, '<a href="$2">$1</a>');

        html = html.replace(/([^\n])\n([^\n])/g, '$1 $2');
        html = html.replace(/\n{3,}/g, '<br /><br />');
        html = html.replace(/\n+/g, '<br />');
        html = html.replace(/<\/ul>[ \n]*<br \/>/g, '</ul>');

        html = imgcode + html + '<span style="clear:both;width: 100%;display: block;"></span>';

        preview.html(html);
    });

    $('textarea').keyup();
});
