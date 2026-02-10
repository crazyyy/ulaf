(function ($) {
  "use strict";

  // ======== Логика скрытия/отображения полей в классическом Checkout ========

  if (typeof saphaliSettings.saphaliKeys !== "undefined") {
    var $keys = saphaliSettings.saphaliKeys || [];
  }
  if (typeof saphaliSettings.saphaliSkeys !== "undefined") {
    var $skeys = saphaliSettings.saphaliSkeys || [];
  }

  // ======== Логика админ-панели (Drag & Drop) ========

  // инициализация TableDnD

  $(".myTable").tableDnD({
    onDragClass: "sorthelper",
    onDrop: function (table, row) {
      var data = new Object();
      data.data = new Object();
      data.key = $(table).find("tr td input").attr("rel");
      $(row).fadeOut("fast").fadeIn("slow");

      $(table)
        .find("tr")
        .each(function (i, e) {
          var id = $(e).find("td input[id*='order_count']").attr("id");
          data.data[i] = id;
          $(e)
            .find("input#" + id)
            .val(i);
        });
    },
  });

  // добавить строку
  ["billing", "shipping", "order"].forEach(function (val) {
    if (0)
      $("#add_" + val + "_field").on("click", function () {
        var counter = $(this)
          .closest("tbody")
          .find('[name*="' + val + '["].order_count').length
          ? $(this)
              .closest("tbody")
              .find('[name*="' + val + '["].order_count:last')
              .val()
          : 0;
        var row =
          "<tr>" +
          '<td><input type="text" name="' +
          val +
          '[new][key][]" value="" /></td>' +
          '<td><input type="text" name="' +
          val +
          '[new][label][]" value="" /></td>' +
          '<td><input type="text" name="' +
          val +
          '[new][placeholder][]" value="" /></td>' +
          '<td><input type="checkbox" name="' +
          val +
          '[new][clear][]" value="1" /></td>' +
          '<td><input type="text" name="' +
          val +
          '[new][class][]" value="" /></td>' +
          '<td>Select <input type="radio" name="' +
          val +
          '[new][type]" value="select"><br>' +
          'Radio <input type="radio" name="' +
          val +
          '[new][type]" value="radio"><br>' +
          'Checkbox <input type="radio" name="' +
          val +
          '[new][type]" value="checkbox"><br>' +
          'Textarea <input type="radio" name="' +
          val +
          '[new][type]" value="textarea"><br>' +
          'Text <input type="radio" name="' +
          val +
          '[new][type]" value=""></td>' +
          '<td><input type="number" name="' +
          val +
          '[new][order][]" value="' +
          (parseInt(counter, 10) + 1) +
          '" class="order_count" /></td>' +
          '<td><input type="checkbox" name="' +
          val +
          '[new][required][]" /></td>' +
          '<td><input type="checkbox" name="' +
          val +
          '[new][public][]" /></td>' +
          '<td><select multiple="multiple" width="120px" name="' +
          val +
          '[new][payment_method][]">\
                            <option selected value="0">'+saphaliSettings.all+'</option></select></td>' +
          '<td><select multiple="multiple" width="120px" name="' +
          val +
          '[new][shipping_method][]">\
                            <option selected value="0">'+saphaliSettings.all+'</option></select></td>' +
          '<td><button class="button remove-row">X</button></td>' +
          "</tr>";
        // $('#billing_fields_table .myTable').append(row);
        $(this).closest("tr").before(row);
        $("#billing_fields_table").tableDnDUpdate();
      });
  });

  // удалить строку
  if (0)
    $("table").on("click", ".remove-row", function (e) {
      e.preventDefault();
      var val = $(this)
        .closest("tr")
        .find("input")
        .attr("name")
        .match(/^[^\[]+/)[0];
      var obj_r = $(this)
        .closest("tbody")
        .find('[name*="' + val + '["].order_count');
      $(this).closest("tr").remove();
      obj_r.each(function (i, e) {
        console.log(i);
        $(e).val(i);
      });
      // $(".myTable").tableDnDUpdate();
    });

  // ======== Другое ========
  $.fn.tipTip=function(options){var defaults={activation:"hover",keepAlive:false,maxWidth:"200px",edgeOffset:3,defaultPosition:"bottom",delay:400,fadeIn:200,fadeOut:200,attribute:"title",content:false,enter:function(){},exit:function(){}};var opts=$.extend(defaults,options);if($("#tiptip_holder").length<=0){var tiptip_holder=$('<div id="tiptip_holder" style="max-width:'+opts.maxWidth+';"></div>');var tiptip_content=$('<div id="tiptip_content"></div>');var tiptip_arrow=$('<div id="tiptip_arrow"></div>');$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>')))}else{var tiptip_holder=$("#tiptip_holder");var tiptip_content=$("#tiptip_content");var tiptip_arrow=$("#tiptip_arrow")}return this.each(function(){var org_elem=$(this);if(opts.content){var org_title=opts.content}else{var org_title=org_elem.attr(opts.attribute)}if(org_title!=""){if(!opts.content){org_elem.removeAttr(opts.attribute)}var timeout=false;if(opts.activation=="hover"){org_elem.hover(function(){active_tiptip()},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}else if(opts.activation=="focus"){org_elem.focus(function(){active_tiptip()}).blur(function(){deactive_tiptip()})}else if(opts.activation=="click"){org_elem.click(function(){active_tiptip();return false}).hover(function(){},function(){if(!opts.keepAlive){deactive_tiptip()}});if(opts.keepAlive){tiptip_holder.hover(function(){},function(){deactive_tiptip()})}}function active_tiptip(){opts.enter.call(this);tiptip_content.html(org_title);tiptip_holder.hide().removeAttr("class").css("margin","0");tiptip_arrow.removeAttr("style");var top=parseInt(org_elem.offset()['top']);var left=parseInt(org_elem.offset()['left']);var org_width=parseInt(org_elem.outerWidth());var org_height=parseInt(org_elem.outerHeight());var tip_w=tiptip_holder.outerWidth();var tip_h=tiptip_holder.outerHeight();var w_compare=Math.round((org_width-tip_w)/2);var h_compare=Math.round((org_height-tip_h)/2);var marg_left=Math.round(left+w_compare);var marg_top=Math.round(top+org_height+opts.edgeOffset);var t_class="";var arrow_top="";var arrow_left=Math.round(tip_w-12)/2;if(opts.defaultPosition=="bottom"){t_class="_bottom"}else if(opts.defaultPosition=="top"){t_class="_top"}else if(opts.defaultPosition=="left"){t_class="_left"}else if(opts.defaultPosition=="right"){t_class="_right"}var right_compare=(w_compare+left)<parseInt($(window).scrollLeft());var left_compare=(tip_w+left)>parseInt($(window).width());if((right_compare&&w_compare<0)||(t_class=="_right"&&!left_compare)||(t_class=="_left"&&left<(tip_w+opts.edgeOffset+5))){t_class="_right";arrow_top=Math.round(tip_h-13)/2;arrow_left=-12;marg_left=Math.round(left+org_width+opts.edgeOffset);marg_top=Math.round(top+h_compare)}else if((left_compare&&w_compare<0)||(t_class=="_left"&&!right_compare)){t_class="_left";arrow_top=Math.round(tip_h-13)/2;arrow_left=Math.round(tip_w);marg_left=Math.round(left-(tip_w+opts.edgeOffset+5));marg_top=Math.round(top+h_compare)}var top_compare=(top+org_height+opts.edgeOffset+tip_h+8)>parseInt($(window).height()+$(window).scrollTop());var bottom_compare=((top+org_height)-(opts.edgeOffset+tip_h+8))<0;if(top_compare||(t_class=="_bottom"&&top_compare)||(t_class=="_top"&&!bottom_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_top"}else{t_class=t_class+"_top"}arrow_top=tip_h;marg_top=Math.round(top-(tip_h+5+opts.edgeOffset))}else if(bottom_compare|(t_class=="_top"&&bottom_compare)||(t_class=="_bottom"&&!top_compare)){if(t_class=="_top"||t_class=="_bottom"){t_class="_bottom"}else{t_class=t_class+"_bottom"}arrow_top=-12;marg_top=Math.round(top+org_height+opts.edgeOffset)}if(t_class=="_right_top"||t_class=="_left_top"){marg_top=marg_top+5}else if(t_class=="_right_bottom"||t_class=="_left_bottom"){marg_top=marg_top-5}if(t_class=="_left_top"||t_class=="_left_bottom"){marg_left=marg_left+5}tiptip_arrow.css({"margin-left":arrow_left+"px","margin-top":arrow_top+"px"});tiptip_holder.css({"margin-left":marg_left+"px","margin-top":marg_top+"px"}).attr("class","tip"+t_class);if(timeout){clearTimeout(timeout)}timeout=setTimeout(function(){tiptip_holder.stop(true,true).fadeIn(opts.fadeIn)},opts.delay)}function deactive_tiptip(){opts.exit.call(this);if(timeout){clearTimeout(timeout)}tiptip_holder.fadeOut(opts.fadeOut)}}})};
  $(".tips, .help_tip").tipTip({
    attribute: "data-tip",
    fadeIn: 50,
    fadeOut: 50,
    delay: 200,
  });
  $('input[value="billing_booking_delivery_t"]').closest("tr").hide();

  $("body").on("click", ".delete-option", function () {
    $(this).closest("span:not('.delete-option')").remove();
  });
  
  $("body").on("click", ".button.add_option", function () {
    $(this).before(
      ' <span><br /><input type="text" class="options" value="" name="billing[' +
        $(this).attr("rel") +
        "][options][option-" +
        ($(this).parent().find("input").length + 1) +
        ']"/><span class="delete-option" style="cursor:pointer;border:1px solid">' +
        saphaliSettings.delete +
        "</span></span>"
    );
  });
  $("body").on("click", 'input[type="radio"]', function () {
    if ($(this).val() == "select" || $(this).val() == "radio") {
      if (
        typeof $(this).closest("tr").find("td:first input") != "undefined" &&
        $(this).closest("tr").find("td:first input").attr("disabled") ==
          "disabled"
      ) {
      } else {
        var obj = $(this).closest("tr");
        obj.find("td").css("border-bottom", "none");
        var val = obj.find("input")
        .attr("name")
        .match(/^[^\[]+/)[0];
        var indx = obj
          .attr("class")
          .split(" ")[0]
          .replace("parrent_td_option", "");
        if (!obj.parent().find("tr.tr_td_option" + indx).length) {
          obj.addClass("parrent_td_option" + $(".button.add_option").length);
          var firstInput = obj.find("td:first input");
          if (val +"[new_fild][name][]" != firstInput.attr("name"))
            obj.after(
              '<tr style="border-top:0" class="tr_td_option' +
                $(".button.add_option").length +
                '" ><td  style="border-top:0;padding-left: 72%;" colspan="9"> <span><input class="options" type="text" value="" name="'+val+'[' +
                firstInput.val() +
                '][options][option-1]"/><span class="delete-option" style="cursor:pointer;border:1px solid">' +
                saphaliSettings.delete +
                '</span></span> <div class="button add_option" rel="' +
                firstInput.val() +
                '">'+saphaliSettings.add2+'</div></td></tr>'
            );
          else
            obj.after(
              '<tr style="border-top:0" class="tr_td_option' +
                $(".button.add_option").length +
                '" ><td  style="border-top:0;padding-left: 72%;" colspan="9"> <span><input class="options" type="text" value="" name="'+val+'[new_fild][options][option-1]"/><span class="delete-option" style="cursor:pointer;border:1px solid">' +
                saphaliSettings.delete +
                '</span></span> <div class="button add_option" rel="new_fild">'+saphaliSettings.add2+'</div></td></tr>'
            );
        }
      }
    } else {
      var obj = $(this).closest("tr");
      if (obj.find("td").attr("style") != "") {
        obj.find("td").attr("style", "");
        var text = obj.attr("class"); //parrent_td_option
        if (typeof text != "undefined") {
          text = text.replace(/parrent_td_option/g, "");
          $("tr.tr_td_option" + text).remove();
          obj.attr("class", "");
        }
      }
    }
  });

  $("body").on("blur", "input.options", function () {
    
    var $input = $(this);
    var baseName = $input.data('base-name');

    // Сохранение исходного имени, если ещё не сохранено
    if (!baseName) {
        baseName = $input.attr('name');
        $input.data('base-name', baseName);
    }

    var enteredValue = $input.val().trim();

    if (enteredValue !== "") {
        // Подсчёт количества существующих опций
        // var optionCount = $('input.options').filter(function() {
        //     return $(this).data('base-name') === baseName;
        // }).length;

        // Формируем новое имя с уникальным индексом
        var newName = baseName + "[" + enteredValue + "]"; /* [option-" + optionCount + "] */
        

        // Обновляем атрибут name
        $input.attr('name', newName);
    } else {
        // Восстановление исходного имени, если поле пустое
        $input.attr('name', baseName);
    }
  });

  var options_payment_method = "",  options_shipping_method = "";
  $.each($keys, function (i, e) {
    options_payment_method += '<option value="' + i + '">' + e + "</option>";
  });

  $.each($skeys, function (i, e) {
    options_shipping_method += '<option value="' + i + '">' + e + "</option>";
  });
  var fild_pm;
  $("body").on("click", ".button#billing", function () {
    var obj = $(this).closest("tr");

    fild_pm =
      '<td>\
                <select multiple="multiple" width="120px" name="billing[new_fild][payment_method][]">\
                    <option selected value="0">'+saphaliSettings.all+'</option>\
                    ' +
      options_payment_method +
      "\
                </select>\
                </td>" +
      '<td>\
                <select multiple="multiple" width="120px" name="billing[new_fild][shipping_method][]">\
                    <option selected value="0">'+saphaliSettings.all+'</option>\
                    ' +
      options_shipping_method +
      "\
                </select>\
                </td>";
    var counter = obj
      .closest("tbody")
      .find('tr td input[id*="order_count"]:last').length
      ? obj.closest("tbody").find('tr td input[id*="order_count"]:last').val()
      : 0;
    obj.html(
      '<td><input value="billing_new_fild' +
        (parseInt(counter, 10) + 1) +
        '" type="text" name="billing[new_fild][name][]" /></td><td><input value="" type="text" name="billing[new_fild][label][]" /></td><td><input value="" type="text" name="billing[new_fild][placeholder][]" /></td><td><input type="checkbox" name="billing[new_fild][clear][]" /></td><td><input value="" type="text" name="billing[new_fild][class][]" /></td><td>	Select <input type="radio" value="select" name="billing[new_fild][type][][' +
        (parseInt(counter, 10) + 1) +
        ']"><br>Radio <input type="radio" value="radio" name="billing[new_fild][type][][' +
        (parseInt(counter, 10) + 1) +
        ']"><br>Checkbox <input type="radio" value="checkbox" name="billing[new_fild][type][][' +
        (parseInt(counter, 10) + 1) +
        ']"><br>	Textarea <input type="radio" value="textarea" name="billing[new_fild][type][][' +
        (parseInt(counter, 10) + 1) +
        ']"><br>	Text <input type="radio" value="" name="billing[new_fild][type][][' +
        (parseInt(counter, 10) + 1) +
        ']" checked="checked"></td><td><input checked type="checkbox" name="billing[new_fild][required][]" /></td><td><input checked type="checkbox" name="billing[new_fild][public][]" /></td>' +
        fild_pm +
        '<td><input id="order_count_billing_' +
        (parseInt(counter, 10) + 1) +
        '" rel="sort_order" type="hidden" name="billing[new_fild][order][]" value="' +
        (parseInt(counter, 10) + 1) +
        '" /><input type="button" class="button billing_delete" value="' +
        saphaliSettings.delete +
        ' -"/></td>'
    );
    obj.removeClass("nodrop nodrag");
    obj.after(
      '<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="billing" value="Добавить +"/></td></tr>'
    );
    $("#the-list-billing").tableDnDUpdate();
  });
  $("body").on("click", ".button#shipping", function () {
    var obj = $(this).closest("tr");
    fild_pm =
      '<td>\
                <select multiple="multiple" width="120px" name="shipping[new_fild][payment_method][]">\
                    <option selected value="0">'+saphaliSettings.all+'</option>\
                     ' +
      options_payment_method +
      "\
                </select>\
                </td>" +
      '<td>\
                <select multiple="multiple" width="120px" name="shipping[new_fild][shipping_method][]">\
                    <option selected value="0">'+saphaliSettings.all+'</option>\
                   ' +
      options_shipping_method +
      "\
                </select>\
                </td>";
    var counter = obj
      .closest("tbody")
      .find('tr td input[id*="order_count"]:last').length
      ? obj.closest("tbody").find('tr td input[id*="order_count"]:last').val()
      : 0;
    obj.html(
      '<td><input value="shipping_new_fild' +
        (parseInt(counter, 10) + 1) +
        '" type="text" name="shipping[new_fild][name][]" /></td><td><input value="" type="text" name="shipping[new_fild][label][]" /></td><td><input value="" type="text" name="shipping[new_fild][placeholder][]" /></td><td><input type="checkbox" name="shipping[new_fild][clear][]" /></td><td><input value="" type="text" name="shipping[new_fild][class][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][required][]" /></td><td><input checked type="checkbox" name="shipping[new_fild][public][]" /></td>' +
        fild_pm +
        '<td><input id="order_count_shipping_' +
        (parseInt(counter, 10) + 1) +
        '" rel="sort_order" type="hidden" name="shipping[new_fild][order][]" value="' +
        (parseInt(counter, 10) + 1) +
        '" /><input type="button" class="button billing_delete" value="' +
        saphaliSettings.delete +
        ' -"/></td>'
    );
    obj.removeClass("nodrop nodrag");
    obj.after(
      '<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="shipping" value="Добавить +"/></td></tr>'
    );
    $("#the-list-shipping").tableDnDUpdate();
  });
  $("body").on("click", ".button#order", function () {
    var obj = $(this).closest("tr");
    var counter = obj
      .closest("tbody")
      .find('tr td input[id*="order_count"]:last').length
      ? obj.closest("tbody").find('tr td input[id*="order_count"]:last').val()
      : 0;
    obj.html(
      '<td><input value="order_new_fild' +
        (parseInt(counter, 10) + 1) +
        '" type="text" name="order[new_fild][name][]" /></td><td><input value="" type="text" name="order[new_fild][label][]" /></td><td><input value="" type="text" name="order[new_fild][placeholder][]" /></td><td><input value="" type="text" name="order[new_fild][class][]" /></td><td><input checked type="text" name="order[new_fild][type][]" /></td><td><input checked type="checkbox" name="order[new_fild][public][]" /></td><td><input id="order_count_' +
        (parseInt(counter, 10) + 1) +
        '" rel="sort_order" type="hidden" name="order[new_fild][order][]" value="' +
        (parseInt(counter, 10) + 1) +
        '" /><input type="button" class="button billing_delete" value="' +
        saphaliSettings.delete +
        ' -"/></td>'
    );
    obj.removeClass("nodrop nodrag");
    obj.after(
      '<tr  class="nodrop nodrag"><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="button" class="button" id="order" value="' +
        saphaliSettings.add +
        ' +"/></td></tr>'
    );
    $("#the-list").tableDnDUpdate();
  });

  $("body").on("click", ".button.billing_delete", function (e) {
    e.preventDefault();
    var obj = $(this).closest("tr");
    var obj_r = obj.closest("tbody");
    obj.remove();
    obj_r.find("tr").each(function (i, e) {
      $(e).find("td input[id*='order_count']").val(i);
    });
  });
})(jQuery);
