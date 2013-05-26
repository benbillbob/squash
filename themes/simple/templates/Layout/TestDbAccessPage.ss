<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">$Content</div>
	</article>
		<% if $TestDbAccess %>
			$TestDbAccess
		<% end_if %>
		<% if $TestDbAccess_Test %>
			$TestDbAccess_Test
		<% end_if %>

		$Form
		$PageComments
</div>
