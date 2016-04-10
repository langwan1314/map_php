/**
 * 创建分页控件
 * 
 * @param pageSize
 *            页面大小，规定写10
 * @param total
 *            总记录数用于计算多少页
 * @param id
 *            html上的分页的ul 的id
 * @param onPageClicked
 *            分页按钮点击事件
 */
function createPage(pageSize, total, id, onPageClicked) {
	jQuery("#" + id).jBootstrapPage({
		pageSize : pageSize,
		total : total,
		maxPageButton : 10,
		onPageClicked : onPageClicked
	});
}
function createPage4Button(pageSize, total, id, onPageClicked, maxPageButton) {
	jQuery("#" + id).jBootstrapPage({
		pageSize : pageSize,
		total : total,
		maxPageButton : maxPageButton,
		onPageClicked : onPageClicked
	});
}

/** 以下是插件js* */
(function($) {
	$.fn.jBootstrapPage = function(config) {
		if (this.size() != 1)
			$.error('请为这个插件提供一个唯一的编号');
		var c = {
			pageSize : 10,
			total : 0,
			maxPages : 1,
			realPageCount : 1,
			lastSelectedIndex : 1,
			selectedIndex : 1,
			maxPageButton : 3,
			onPageClicked : null
		};
		var firstBtn, preBtn, nextBtn, lastBtn;
		return this
				.each(function() {
					var $this = $(this);
					if (config)
						$.extend(c, config);
					init();
					bindALL();
					function init() {
						$this.find('li').remove();
						c.maxPages = Math.ceil(c.total / c.pageSize);
						if (c.maxPages < 1)
							return;
						$this
								.append('<li class="disabled"><a class="first" href="#">首页</a></li>');
						$this
								.append('<li class="disabled"><a class="pre" href="#">〈</a></li>');
						var pageCount = c.maxPages < c.maxPageButton ? c.maxPages
								: c.maxPageButton;
						var pNum = 0;
						for (var index = 1; index <= pageCount; index++) {
							pNum++;
							$this.append('<li class="page" pNum="' + pNum
									+ '"><a href="#" page="' + index + '">'
									+ index + '</a></li>');
						}
						if (pageCount == 1) {
							$this
									.append('<li  class="disabled"><a class="next" href="#">〉</a></li>');
							$this
									.append('<li  class="disabled"><a class="last" href="#">末页</a></li>');
						} else {
							$this
									.append('<li ><a class="next" href="#">〉</a></li>');
							$this
									.append('<li><a class="last" href="#">末页</a></li>');
						}

						$this.find('li:nth-child(3)').addClass('active');
						firstBtn = $this.find('li a.first').parent();
						preBtn = $this.find('li a.pre').parent();
						lastBtn = $this.find('li a.last').parent();
						nextBtn = $this.find('li a.next').parent();
					}

					function mathPrePage(currButtonNum, currPage, maxPage,
							showPage) {
						if (maxPage < 1)
							return;
						// 选中的按钮大于中间数，就进一位
						var middle = Math.ceil(showPage / 2); // 4
						// 4 > 3
						// 5 - 4 + 3
						if (currButtonNum != currPage && currButtonNum < middle) {
							$this.find('li.page').remove();
							// 1 2 3 4 5 6 7 8 9 10
							var endPages = currPage + Math.floor(middle / 2);
							if (endPages < c.maxPageButton)
								endPages = c.maxPageButton + 1;
							var startPages = endPages - c.maxPageButton;
							if (startPages <= 0)
								startPages = 1;
							if (endPages - startPages >= c.maxPageButton) {
								var d = endPages - startPages - c.maxPageButton;

								if (d == 0)
									d = 1;
								endPages -= d;
							}
							var pNum = 0;
							var html = '';
							for (var index = startPages; index <= endPages; index++) {
								pNum++;
								html += '<li class="page" pNum="' + pNum
										+ '"><a href="#" page="' + index + '">'
										+ index + '</a></li>';
							}
							$this.find('li:nth-child(2)').after(html);
							bindPages();
						} else if (currButtonNum == "1") {// 点击首页

							$this.find('li.page').remove();
							firstBtn.addClass("disabled");
							preBtn.addClass("disabled");

							var startPages = 1;
							var endPages = c.maxPageButton;
							if (c.pageSize > maxPage) {
								endPages = maxPage;
							}

							var pNum = 0;
							var html = '';
							for (var index = startPages; index <= endPages; index++) {
								pNum++;

								html += '<li class="page" pNum="' + pNum
										+ '"><a href="#" page="' + index + '">'
										+ index + '</a></li>';

							}
							$this.find('li:nth-child(2)').after(html);

							nextBtn.removeClass("disabled");
							lastBtn.removeClass("disabled");
							preBtn.next().addClass('active');
							bindPages();

						}
					}

					function mathNextPage(currButtonNum, currPage, maxPage,
							showPage) {
						if (maxPage < 1)
							return;
						var offsetRight = 2;
						// 选中的按钮大于中间数，就进一位
						var middle = showPage - 1; // 4
						// 4 > 3
						// 5 - 4 + 3
						if ((currButtonNum != currPage + 1 || maxPage > showPage)
								&& currButtonNum >= middle) {
							// 显示后面2个按钮
							var startPages = currPage - offsetRight;
							var endPages = currPage + middle;
							endPages = endPages >= maxPage ? maxPage : endPages;
							if (endPages <= c.maxPageButton)
								endPages = c.maxPageButton;
							if (endPages - startPages >= c.maxPageButton) {
								var d = endPages - startPages - c.maxPageButton;
								endPages -= d;
							}
							if (endPages == maxPage)
								endPages++;
							if (endPages - startPages < c.maxPageButton) {
								var d = c.maxPageButton
										- (endPages - startPages);
								startPages -= d;
							}
							var pNum = 0;
							var html = '';
							for (var index = startPages; index < endPages; index++) {
								pNum++;

								html += '<li class="page" pNum="' + pNum
										+ '"><a href="#" page="' + index + '">'
										+ index + '</a></li>';
							}
							$this.find('li.page').remove();
							$this.find('li:nth-child(2)').after(html);
							bindPages();

						}
						
					}

					function onClickPage(pageBtn) {
						c.lastSelectedIndex = c.selectedIndex;
						if (pageBtn == 1) {
							c.selectedIndex = pageBtn;
						} else {
							c.selectedIndex = parseInt(pageBtn.text());
						}
						if (c.onPageClicked) {
							c.onPageClicked.call(this, $this,
									c.selectedIndex - 1);
						}
						// 点击第一页不变
						$this.find('li.active').removeClass('active');
						pageBtn.parent().addClass('active');  //点击首页这里报错（应该是跳到1）
						if (c.selectedIndex > 1) {
							if (preBtn.hasClass('disabled')) {
								firstBtn.removeClass("disabled");
								preBtn.removeClass("disabled");
								bindFirsts();
							}
						} else {
							if (!preBtn.hasClass('disabled')) {
								firstBtn.addClass("disabled");
								preBtn.addClass("disabled");
							}
						}
						if (c.selectedIndex >= c.maxPages) {
							if (!nextBtn.hasClass('disabled')) {
								nextBtn.addClass("disabled");
								lastBtn.addClass("disabled");
							}
						} else {
							if (nextBtn.hasClass('disabled')) {
								nextBtn.removeClass("disabled");
								lastBtn.removeClass("disabled");
								bindLasts();
							}
						}
					}

					function onPageBtnClick($_this) {
						var selectedText = $_this.text();
						var selectedBtn = $this.find('li.active').find('a');
						if (selectedText == '〉') {
							var selectedIndex = parseInt(selectedBtn.text());
							var selectNum = parseInt($this.find('li.active')
									.attr('pNum')) + 1;
							if (isNaN(selectNum)) {// 如果点击首页
								selectedIndex = 1
								selectNum = 2;
							}
							if (selectNum > c.maxPageButton)
								selectNum = c.maxPageButton - 1;
							if (selectedIndex > 0) {
								mathNextPage(selectNum, selectedIndex,
										c.maxPages, c.maxPageButton);
								selectedBtn = $this.find('li.page')
										.find(
												'a[page="'
														+ (selectedIndex + 1)
														+ '"]');
							}
						} else if (selectedText == '首页') { // 首页
							selectNum = 1;
							mathPrePage(selectNum, selectNum, c.maxPages,
									c.maxPageButton);
							selectedBtn = $this.find('li.page').find(
									'a[page="1"]');
						} else if (selectedText == '末页') { // 末页
							selectNum = c.maxPages;
							mathNextPage(selectNum, selectNum, c.maxPages,
									c.maxPageButton);
							selectedBtn = $this.find('li.page').find(
									'a[page="' + (c.maxPages) + '"]');
						} else if (selectedText == '〈') {
							var selectedIndex = parseInt(selectedBtn.text()) - 1;
							var selectNum = parseInt($this.find('li.active')
									.attr('pNum')) - 1;
							if (selectNum < 1)
								selectNum = 1;
							mathPrePage(selectNum, selectedIndex, c.maxPages,
									c.maxPageButton);
							selectedBtn = $this.find('li.page').find(
									'a[page="' + (selectedIndex) + '"]');
						} else {
							selectedBtn = $_this;
						}
						onClickPage(selectedBtn);
					}

					function bindPages() {
						$this.find("li.page a").each(function() {
							if ($(this).parent().hasClass('disabled'))
								return;
							$(this).on('pageClick', function(e) {
								onPageBtnClick($(this));
							});
						});
						$this.find("li.page a").click(function(e) {
							e.preventDefault();
							$(this).trigger('pageClick', e);
						});
					}

					function bindFirsts() {
						$this.find("li a.first,li a.pre").each(function() {
							if ($(this).parent().hasClass('disabled'))
								return;
							$(this).unbind('pageClick');
							$(this).on('pageClick', function(e) {
								onPageBtnClick($(this));
							});
						});
					}

					function bindLasts() {
						$this.find("li a.last,li a.next").each(function() {
							if ($(this).parent().hasClass('disabled'))
								return;
							$(this).unbind('pageClick');
							$(this).on('pageClick', function(e) {
								onPageBtnClick($(this));
							});
						});
					}

					function bindALL() {
						$this
								.find(
										"li.page a,li a.first,li a.last,li a.pre,li a.next")
								.each(function() {
									if ($(this).parent().hasClass('disabled'))
										return;
									$(this).on('pageClick', function(e) {
										onPageBtnClick($(this));
									});
								});
						$this
								.find(
										"li.page a,li a.first,li a.last,li a.pre,li a.next")
								.click(function(e) {
									e.preventDefault();
									if ($(this).parent().hasClass('disabled'))
										return;
									$(this).trigger('pageClick', e);
								});
					}
				});
	};
})(jQuery);