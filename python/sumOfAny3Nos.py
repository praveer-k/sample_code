def sumofTwoEqualsX(arr, x):
    first = 0
    last = len(arr)-1
    while(first<last):
        print arr[first], " + ", arr[last], " = ", arr[first]+arr[last]
        if arr[first]+arr[last]==x:
            return True
        elif arr[first]+arr[last]<x:
            first+=1
        else:
            last-=1
    return False

def sumOfAny3NosEqualsZero(arr):
    for i in range(len(arr)):
        print arr[i]
        newArr = arr[:i] + arr[i+1:]
        print newArr
        if sumofTwoEqualsX(newArr, -arr[i])==True:
            return True

def main():
    arr = [-5,-4,-3,4,5,6]
    if sumOfAny3NosEqualsZero(arr):
        print "Yes"
    else:
        print "No"
    return 0

if __name__ == "__main__":
    main()
