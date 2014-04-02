<% if Tags %>
<div class="sidebarBox" id="SidebarModuleProductGroupTags">
	<h3>Tags</h3>
	<ul>
		<li class="First showAll"><a href="$Link" rel="">show all</a></li>
		<% with/loop Tags %><li class="$Last $LinkingMode">
		<% include EcommerceProductTagItem %>
		</li><% end_with/loop %>
	</ul>

</div>
<% end_if %>
