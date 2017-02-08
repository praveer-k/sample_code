def push_to_end(arr):
    i = len(arr)-2
    while i>=0:
        if arr[i]==0:
            temp = arr.pop(i)
            arr.append(temp)
        i-=1
    return arr

arr = [1, 0, 2, 0, 4, 5, 0 , 0, 1]
print push_to_end(arr)
