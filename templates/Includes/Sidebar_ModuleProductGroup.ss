<% if Tags %>
<div class="sidebarBox" id="SidebarModuleProductGroupTags">
	<h3>Tags</h3>
	<ul>
		<li class="First showAll"><a href="$Link" rel="">show all</a></li>
		<% loop Tags %><li class="$Last $LinkingMode">
		<% include EcommerceProductTagItem %>
		</li><% end_loop %>
	</ul>

</div>
<% end_if %>
