<div class="sidebarBox" id="SidebarChildren">
	<% if Tags %>
	<h3>In the <i>$MenuTitle</i> section</h3>
	<ul>
		<% with/loop Tags %><li class="$FirstLast $LinkingMode">
		<% include EcommerceProductTagItem %>
		</li><% end_with/loop %>
	</ul>
	<% end_if %>
</div>
