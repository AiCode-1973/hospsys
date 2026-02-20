import zipfile
import xml.etree.ElementTree as ET
import sys

def get_xlsx_data(filename):
    try:
        z = zipfile.ZipFile(filename)
        # Get shared strings
        try:
            strings_xml = z.read('xl/sharedStrings.xml')
            strings_root = ET.fromstring(strings_xml)
            namespace = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
            strings = []
            for si in strings_root.findall('ns:si', namespace):
                t = si.find('ns:t', namespace)
                if t is not None:
                    strings.append(t.text)
                else:
                    # Handle formatted text
                    parts = si.findall('.//ns:t', namespace)
                    strings.append(''.join([p.text for p in parts if p.text]))
        except KeyError:
            strings = []

        # Get sheet 1 data
        sheet_xml = z.read('xl/worksheets/sheet1.xml')
        sheet_root = ET.fromstring(sheet_xml)
        namespace = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
        
        data = []
        for row in sheet_root.findall('.//ns:row', namespace):
            row_data = []
            # We need to handle missing cells if we want a proper grid, 
            # but for extraction, just getting the values is a start.
            for cell in row.findall('ns:c', namespace):
                v_tag = cell.find('ns:v', namespace)
                if v_tag is not None:
                    val = v_tag.text
                    if cell.get('t') == 's':
                        val = strings[int(val)]
                    row_data.append(val)
                else:
                    row_data.append("")
            data.append(row_data)
        return data
    except Exception as e:
        print(f"Error: {e}")
        return None

if __name__ == "__main__":
    file_path = 'CARRINHO DA TOMOGRAFIA_NOVO.xlsx'
    data = get_xlsx_data(file_path)
    if data:
        for row in data:
            print("|".join([str(x) if x is not None else "" for x in row]))
