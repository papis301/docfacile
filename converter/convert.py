import sys
from pdf2docx import Converter
try:
    cv = Converter(sys.argv[1])
    cv.convert(sys.argv[2])
    cv.close()
except Exception as ex:
    print(ex, file=sys.stderr)
    sys.exit(1)
