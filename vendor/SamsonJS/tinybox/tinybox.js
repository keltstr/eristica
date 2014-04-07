/**
 * Вывести контейнер на черном фоне в центре экрана
 */
var tinybox = function( selector, oneClickClose )
{		
	var tinyboxObj = {};
	
	// Safely get object
	selector = s( selector );		
	
	// If we have dom elements found
	if( !selector.length ) return;
	
	// Work with first element
	selector = selector.elements[0];
	
	// Add tinybox class
	selector.addClass('__samsonjs-tinybox-popup');		
		
	// Create BG
	var bg_container = s('<div class="sjs-lightbox-bg" style="display:none;position:fixed;"></div>');
		
	// Append BG
	s(document.body).append( bg_container );		
	
	/**
	 * Обработчик пересчета позиции элемента
	 */
	tinyboxObj.calculate = function()
	{				
		// Получим размеры контейнера
		var lb_width = selector.width();
		var lb_height = selector.height();
		
		// Получим размеры документа
		var bd_width =  s.pageWidth();
		var bd_height = s.pageHeight();
						
		// Рассчитаем новые "центровые" координаты
		var lb_top = (bd_height - lb_height) / 2;
		var lb_left = (bd_width - lb_width) / 2;
		
		if(lb_top < 0 ) lb_top = 0;
				
		// Разместим наш контейнер по новім координатам
		selector.top( lb_top + "px");
		selector.left( lb_left + "px");
		
		// Рассчитаем высоту контейнера фона
		bg_container_height = s(document.body).height() + 1000;
		
		// Изменим размер контейнера "темного" фона
		bg_container.height(  bg_container_height + "px");		
	};
	
	/** Обработчик удаления "коробочки" */
    tinyboxObj.close = tinyboxObj._close = function()
	{	
		// Уничтожем фоновый контейнер
		bg_container.remove();
		
		// Офистим флаг отображения блока
		tinyboxObj.shown = undefined;
		
		// Уничтожем сам элемент
		selector.remove();	
		
		// unFix body
		//s(document.body).css('position','');		
		//s.pageScrollTop(tinyboxObj.scrollTop);
	};
	
	/** Обработчик прятания "коробочки" */
    tinyboxObj.hide = tinyboxObj._hide = function()
	{		
		// Уничтожем фоновый контейнер
		bg_container.hide();
		
		// Офистим флаг отображения блока
		this.shown = false;
		
		// Уничтожем сам элемент
		selector.hide();
		
		// unFix body
		//s(document.body).css('position','');		
		//s.pageScrollTop(tinyboxObj.scrollTop);
	};
	
	/** Обработчик отображения "коробочки" */
    tinyboxObj.show = tinyboxObj._show = function()
	{
		// show bg
		bg_container.show();
		
		// Fix box
		selector.css('position','fixed');
		
		// On top
		selector.css('z-index','999');		

		// Show box
		selector.show();		
		
		// Calculate size and pos
		tinyboxObj.calculate();		
	};	
	
	// Window resize handler
	window.onresize = tinyboxObj.calculate;
	
	// Closer
	s('.close-button', selector).click( tinyboxObj._hide, true, true );

    // If we must close TB on click anywhere else
	if (oneClickClose)
	{			
		// Повесим обработчик для закрытия 
		s('html').click(function( obj, opt, e )
		{					
			// Получим элемент на который нажали 
			var clickedElement = s(e.srcElement||e.originalTarget);		

			// Если мы нажали НЕ на наш контейнер - закроем
			if( clickedElement != undefined &&
				!clickedElement.hasClass('__samsonjs-tinybox-popup')&&
				!clickedElement.parent('__samsonjs-tinybox-popup').hasClass('__samsonjs-tinybox-popup') ) 
				tinyboxObj.close();
		});
	}
	
	// Show tinybox
	tinyboxObj._show();
	
	// Вернем указатель на себя
	return tinyboxObj;
};