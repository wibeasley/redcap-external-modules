<?php
namespace ExternalModules;
require_once '../../classes/ExternalModules.php';

$prefix = $_GET['prefix'];
$pid = @$_GET['pid'];

if(!ExternalModules::hasProjectSettingSavePermission($prefix)){
	throw new Exception('You do not have permission to get or set rich text files.');
}

$files = ExternalModules::getProjectSetting($prefix, $pid, ExternalModules::RICH_TEXT_UPLOADED_FILE_LIST);

if(!$files){
	$files = [];
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$file = @$_FILES['file'];
	$edocToDelete = @$_POST['edoc-to-delete'];

	if($file){
		$edocId = \Files::uploadFile($file);

		$files[] = [
			'edocId' => $edocId,
			'name' => $file['name']
		];
	}
	else if($edocToDelete){
		ExternalModules::deleteEDoc($edocToDelete);

		for($i=0; $i<count($files); $i++){
			if($files[$i]['edocId'] == $edocToDelete){
				unset($files[$i]);
			}
		}
	}

	ExternalModules::setProjectSetting($prefix, $pid, ExternalModules::RICH_TEXT_UPLOADED_FILE_LIST, $files);
}

?>

<style>
	#external-modules-rich-text-upload-button{
		margin: 5px;
	}

	#external-modules-rich-text-file-table{
		margin-top: 5px;
		border-collapse: collapse;
		width: 100%;
	}

	#external-modules-rich-text-file-table tr{
		border-top: 1px solid #dadada;
	}

	#external-modules-rich-text-file-table td{
		padding: 5px;
	}

	#external-modules-rich-text-file-table form{
		margin-bottom: 0px;
	}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>

<button id="external-modules-rich-text-upload-button">Upload a file</button>

<table id="external-modules-rich-text-file-table">
	<?php
	foreach($files as $file){
		$edocId = $file['edocId'];
		$name = $file['name'];
		$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		?>
		<tr>
			<td><a href="#" data-edoc-file="<?="$edocId.$extension"?>"><?=$name?></a></td>
			<td>
				<form method='POST' enctype='multipart/form-data'>
					<input type="hidden" name="edoc-to-delete" value="<?=$edocId?>">
					<button class="delete">Delete</button>
				</form>
			</td>
		</tr>
		<?php
	}
	?>
</table>

<form id='external-modules-rich-text-form' method='POST' enctype='multipart/form-data' style='display: none'>
	<input name="file" type="file">
</form>

<script>
	$(function() {
		var form = $('#external-modules-rich-text-form')
		var fileInput = form.find('input[type=file]')

		$('#external-modules-rich-text-upload-button').click(function () {
			fileInput.click()
		})

		fileInput.change(function () {
			form.submit()
		})
	})

	$(function(){
		var table = $('#external-modules-rich-text-file-table')

		table.find('a').click(function(e){
			e.preventDefault()
			var file = $(this).data('edoc-file')
			var url = parent.ExternalModules.BASE_URL + 'manager/rich-text/get-file.php?file=' + file + '&prefix=<?=$prefix?>&pid=<?=$pid?>'
			parent.ExternalModules.currentFilePickerCallback(url)
		})

		table.find('button.delete').click(function(){
			return confirm('Are you sure you want to permanently delete this file?')
		})
	})
</script>
