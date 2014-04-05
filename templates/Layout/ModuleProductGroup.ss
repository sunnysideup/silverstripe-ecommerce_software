
<div id="ProductGroup">
	<h1 id="PageTitle">$Title</h1>
	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>
	<div id="ModuleSearchForm">$ModuleSearchForm</div>
<% if Products %>
	<div id="Products" class="category">
		<div class="resultsBar">
			<% if SortLinks %><span class="sortOptions"><% _t('ProductGroup.SORTBY','Sort by') %>
			<% loop SortLinks %><a href="$Link" class="sortlink $Current">$Name</a> <% end_loop %></span><% end_if %>
		</div>
		<ul class="productList">
			<% loop Products %>
			<li class="<% if Authors %> <% loop Authors %> author_$ScreenName <% end_loop %><% end_if %>
			<% if EcommerceProductTags %> <% loop EcommerceProductTags %> filter_$Code <% end_loop %><% end_if %>" id="ModuleProductID{$ID}">
				<a href="$Link" class="moreInfoLink" rel="Explanation$ID" title="code: $Code.ATT">$Title</a>
				<% if EcommerceProductTags %><span class="tags">
					<span class="tagHeading listItemHeading" title="Tag(s)">Tag(s)</span>
					<% loop EcommerceProductTags %><a href="$Link" rel="filter_$Code">$Title</a><% if Last %>.<% else %>, <% end_if %><% end_loop %>
				</span><% end_if %>
				<% if Authors %><span class="authors tags">
					<span class="authorHeading listItemHeading" title="Author(s)">Author:</span>
					<% loop Authors %><a href="#" rel="author_$ScreenName">$ScreenName</a><% if Last %>.<% else %>, <% end_if %><% end_loop %>
				</span><% end_if %>
				<div class="explanation" id="Explanation$ID">
					<p class="shortIntro">$MetaDescription</p>
				</div>
			</li>
			<% end_loop %>
		</ul>
		<div class="clear"><!-- --></div>
	</div>
<% include ProductGroupPagination %>
<% end_if %>
	<% if Form %><div id="FormHolder">$Form</div><% end_if %>
	<% if PageComments %><div id="PageCommentsHolder">$PageComments</div><% end_if %>
</div>

<aside>
	<div id="Sidebar">
		<div class="sidebarTop"></div>
		<% include Sidebar_Cart %>
		<% include Sidebar_ModuleProductGroup %>
		<% include Sidebar_UserAccount %>
		<div class="sidebarBottom"></div>
	</div>
</aside>

