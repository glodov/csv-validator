<?
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
Task - develop module for uploading and testing CSV file for errors.

1. Upload file
1.1. Make HTML form for upload
1.2. Make controller for file upload
2. Parse file
3. Show errors if exist
*/
function fit(array $row)
{
	$result = array();
	if (count($row) === 4 && '1' === $row[0])
	{
		// product group detected
		foreach (array(0, 1) as $i)
		{
			if (!is_numeric($row[$i]))
			{
				$result[] = $i;
			}
		}
		if (!(is_numeric($row[2]) || '' === $row[2]))
		{
			$result[] = 2;
		}
		if (count($result))
		{
			return $result;
		}
		return true;
		//return is_numeric($row[0]) && is_numeric($row[1]) && (is_numeric($row[2]) || '' === $row[2]);
	}
	else if (count($row) > 7 && '0' === $row[0])
	{
		// product detected
		if (is_numeric($row[0]) && is_numeric($row[1]) && is_numeric($row[2]) && is_numeric($row[6]))
		{
			for ($i = 7; $i < count($row); $i++)
			{
				if (!is_numeric($row[$i]) && '' !== $row[$i])
				{
					$result[] = $i;
				}
			}
			if (count($result))
			{
				return $result;
			}
			return true;
		}
		else
		{
			foreach (array(0, 1, 2, 6) as $i)
			{
				if (!is_numeric($row[$i]))
				{
					$result[] = $i;
				}
			}
			return $result;
		}
	}
	else
	{
		// wrong row
		return $result;
	}

	// just for fun, we cannot be here never
	return true;
}


function convert(array $arr)
{
	global $encoding;

	if ('utf-8' === $encoding)
	{
		return $arr;
	}
	$result = array();
	foreach ($arr as $key => $value)
	{
		$result[$key] = mb_convert_encoding($value, 'utf-8', $encoding);
	}
	return $result;
}

$config = array(
	'isHeader' 		=> 0,
	'ignoreEmpty'	=> 1,
	'encoding'		=> 'utf-8',
	'length'		=> 2048,
	'delimeter'		=> ';',
	'enclosure'		=> '"',
	'escape'		=> "\\",
);
$isConfigurable = true;

foreach ($config as $key => $value)
{
	$config[$key] = isset($_POST[$key]) ? $_POST[$key] : $value;
}
extract($config);


$error = array(); // global errors
$cols = array(); // header columns
$rows = array(); // rows with error
$uploaded = false;
$encoding = strtolower($encoding);

if (isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name']))
{
	$uploaded = true;
	// upload file
	// file uploaded to temp
	$_FILES['file']['tmp_name'];

	// parse CSV file
	$f = @fopen($_FILES['file']['tmp_name'], 'r');
	if ($f)
	{
		$i = 0;
		while ($arr = fgetcsv($f, $length, $delimeter, $enclosure, $escape))
		{
			$arr = convert($arr);

			++$i;
			// set header columns if they defined
			if ($isHeader && 1 == $i)
			{
				$cols = $arr;
				continue;
			}

			// determine count of columns in longest line
			if (!$isHeader && count($cols) < count($arr))
			{
				$cols = array_fill(0, count($arr), '-');
			}

			// check for empty row
			if ($ignoreEmpty)
			{
				$empty = false;
				foreach ($arr as $value)
				{
					if (trim($value) !== '')
					{
						$empty = true;
						break;
					}
				}
				if (false === $empty)
				{
					continue; // row fits to empty line which is ignored by config
				}
			}

			if (true !== ($err = fit($arr)))
			{
				if (!count($rows))
				{
					$error[] = 'Erros in sheet found';
				}
				// show error for current line
				$rows[] = array($arr, $err, $i);
			}
		}
		fclose($f);
	}
	else
	{
		// show error: could not open file
		$error[] = 'Could not open file '.$_FILES['file']['name'];
	}
}

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<!--[if IE]> <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> <![endif]-->
		<title>Api tester</title>

<style type="text/css">
body, p, input, select, textarea, button{
	font-family: Helvetica, Arial, sans-serif;
}
.container{
	width: 100%;
}
form{
	margin-bottom: 20px;
	padding-bottom: 20px;
	border-bottom: 1px solid #ccc;
}
label{
	display: block;
	margin-bottom: 10px;
	width: 250px;
	overflow: hidden;
}
label input{
	float: right;
	width: 100px;
}
ul.err{
	font-size: 24px;
	color: red;
}
table{
	width: 100%;
	border-spacing: 2px;
}
table thead td{
	text-align: left;
}
table tr td, table tr th{
	min-width: 40px;
	text-align: left;
	border: 1px solid #eee;
}
table tr .err{
	color: #fff;
	background: red;
}
h1{
	color: green;
}
</style>

	</head>
	<body>

<div class="container">

<form action="" method="post" enctype="multipart/form-data">

<? foreach ($config as $key => $value) : ?>
	<label>
		<?= htmlspecialchars($key) ?>:
		<input type="text" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>" <?= $isConfigurable ? '' : 'disabled="disabled"' ?> />
	</label>
<? endforeach; ?>

	<input type="file" name="file" />
	<button type="submit" name="submit">&raquo;</button>

</form>

<? if (count($error)) : ?>
<ul class="err">
<? foreach ($error as $err) : ?>
	<li><?= htmlspecialchars($err) ?></li>
<? endforeach; ?>
</ul>

<? if (count($rows)) : ?>
<table>
	<thead>
		<tr>
			<th>#</th>
<? foreach ($cols as $value) : ?>
			<td><?= htmlspecialchars($value) ?></td>
<? endforeach; ?>
		</tr>
	</thead>
	<tbody>
<? foreach ($rows as $row): ?>
		<tr>
			<th class="<?= count($row[1]) === 0 ? 'err' : '' ?>"><?= htmlspecialchars($row[2]) ?></th>
<? for ($i = 0; $i < count($cols); $i++) : ?>
			<td class="<?= in_array($i, $row[1]) ? 'err' : '' ?>"><?= htmlspecialchars(isset($row[0][$i]) ? $row[0][$i] : '') ?></td>
<? endfor; ?>
		</tr>
<? endforeach; ?>
	</tbody>
</table>
<? endif; ?>

<? elseif ($uploaded) : ?>

<h1>Congratulations, no errors found!</h1>
<? endif; ?>

	</div>

	</body>
</html>
