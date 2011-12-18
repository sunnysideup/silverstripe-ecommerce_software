<div class="sidebarBox" id="SidebarChildren">
	<% if Tags %>
	<h3>In the <i>$MenuTitle</i> section</h3>
	<ul>
		<% control Tags %><li class="$FirstLast $LinkingMode">
		<% include EcommerceProductTagItem %>
		</li><% end_control %>
	</ul>
	<% end_if %>
</div>
