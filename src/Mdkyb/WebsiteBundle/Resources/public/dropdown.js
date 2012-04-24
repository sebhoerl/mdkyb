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
});
