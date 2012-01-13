<div id="Sidebar">
	<div class="sidebarTop"></div>
	<% include Sidebar_Cart %>
	<% include Sidebar_ModuleProductGroup %>
	<div class="sidebarBottom"></div>
</div>
<div id="ProductGroup">
	<h1 id="PageTitle">$Title</h1>
	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>
	<div id="ModuleSearchForm">$ModuleSearchForm</div>
<% if Products %>
	<div id="Products" class="category">
		<div class="resultsBar">
			<% if SortLinks %><span class="sortOptions"><% _t('ProductGroup.SORTBY','Sort by') %> <% control SortLinks %><a href="$Link" class="sortlink $Current">$Name</a> <% end_control %></span><% end_if %>
		</div>
		<ul class="productList">
			<% control Products %>
			<li class="<% if Authors %> <% control Authors %> author_$ScreenName <% end_control %><% end_if %><% if EcommerceProductTags %> <% control EcommerceProductTags %> filter_$Code <% end_control %><% end_if %>" id="ModuleProductID{$ID}">
				<a href="$Link" class="moreInfoLink" rel="Explanation$ID" title="code: $Code.ATT">$Title</a>
				<% if EcommerceProductTags %><span class="tags">
					<span class="tagHeading listItemHeading" title="Tag(s)">Tag(s)</span>
					<% control EcommerceProductTags %><a href="$Link" rel="filter_$Code">$Title</a><% if Last %>.<% else %>, <% end_if %><% end_control %>
				</span><% end_if %>
				<% if Authors %><span class="authors tags">
					<span class="authorHeading listItemHeading" title="Author(s)">Author:</span> <% control Authors %><a href="#" rel="author_$ScreenName">$ScreenName</a><% if Last %>.<% else %>, <% end_if %><% end_control %>
				</span><% end_if %>
				<div class="explanation" id="Explanation$ID">
					<p>$MetaDescription</p>
					<% include ProductActions %>
					<p class="more"><a href="$Link">more details ...</a></p>
				</div>
			</li>
			<% end_control %>
		</ul>
		<div class="clear"><!-- --></div>
	</div>
<% include ProductGroupPagination %>
<% end_if %>
	<% if Form %><div id="FormHolder">$Form</div><% end_if %>
	<% if PageComments %><div id="PageCommentsHolder">$PageComments</div><% end_if %>
</div>
<div class="clear"><!-- --></div>




