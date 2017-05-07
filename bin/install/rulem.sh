#!/bin/bash -e

#|------------------------------------------------------------------------------
#|	Print a ruler in terminal window with message
#|
#|		set -l _hr..printf assigns the result of the string interpolation to the local variable “_hr”
#|		%*s waits for numeric input to define the width of the string, which in this case will be output #|			as that number of spaces
#|		(tput cols) is replaced with the number of columns in the current terminal as reported by tput
#|			(passed to the %*s)
#|		The variable is then output with sed substitution to	replace the spaces with a - (default) or the #|			desired character
#|    https://gist.github.com/ttscoff/4fef9fb5a945f5748c84
#|------------------------------------------------------------------------------
## Print horizontal ruler with message
rulem ()  {
	if [ $# -eq 0 ]; then
		echo "Usage: rulem MESSAGE [RULE_CHARACTER]"
		return 1
	fi
	# Fill line with ruler character ($2, default "-"), reset cursor, move 2 cols right, print message
	printf -v _hr "%*s" $(tput cols) && echo -en ${_hr// /${2--}} && echo -e "\r\033[2C$1"
}
