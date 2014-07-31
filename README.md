## CSV validator

Validator for CSV import files when working with remote data from another applications.

It takes so much time to find error in CSV file when you import data such price-list from another application if it is not a popular format and you deal with programmer who export this CSV file.

This simple script helps you to localize errors in CSV file. So you just save your time.
So you no need to upload CSV file in your website every time after small mistake fixed. You just write rules in function fit() and upload this script on test server and programmer who makes export to CSV can find his mistakes himself by using this CSV validator.


## Example

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
	
In this CSV file we have product and product group lines.
In product group we have only 4 columns and first one equals `1`, first and second columns must be numbers, third column is number but can be empty also. The fourth column can have any text or symbols.
