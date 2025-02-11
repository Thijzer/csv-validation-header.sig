import sys
# import csv
# import operator
#
#
field = sys.argv[1]
in_file = sys.argv[2]
out_file = sys.argv[3]
encapsulate = sys.argv[4]
delimiter = sys.argv[5]

# with open(in_file) as filter_f, open(out_file, newline='') as data_f:
#   data = csv.DictReader(data_f, encapsulate, delimiter, quoting=csv.QUOTE_ALL, skipinitialspace=True)
#   data_filtered = csv.DictWriter(sys.stdout, fieldnames=data.fieldnames)
#   data_filtered.writeheader()
#   for filter_name in filter_f:
#     for row in data:
#       if filter_name != row[field]:
#         continue
#       else:
#         data_filtered.writerow(row)
#         break


# def sort_csv(csv_filename, types, sort_key_columns):
#     """sort (and rewrite) a csv file.
#     types:  data types (conversion functions) for each column in the file
#     sort_key_columns: column numbers of columns to sort by"""
#     data = []
#     with open(csv_filename, 'rb') as f:
#         for row in csv.reader(f):
#             data.append(convert(types, row))
#     data.sort(key=operator.itemgetter(*sort_key_columns))
#     with open(csv_filename, 'wb') as f:
#         csv.writer(f).writerows(data)

import pandas as pd
import os

df = pd.DataFrame(list())
df.to_csv(out_file);

if os.path.getsize(in_file) == 0:
    quit()

df = pd.read_csv(in_file,sep=delimiter, dtype=str)
sorted_df = df.sort_values(by=[field], ascending=True)
sorted_df.to_csv(out_file, index=False, sep=';')
