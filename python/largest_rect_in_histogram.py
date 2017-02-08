def largest(hist):
    stack = []
    pos = 0
    maxSize = 0
    for i in range(0, len(hist)):
        top = len(stack)-1
        if len(stack)==0 or hist[i]>stack[top][1]:
            stack.append((i, hist[i]))
            print (i, hist[i])
        elif hist[i]<stack[top][1]:
            while len(stack)>0 and hist[i]<stack[top][1]:
                temp = stack.pop()
                size = temp[1]*(i-temp[0])
                maxSize = max(size, maxSize)
                top = len(stack)-1
                print "=-->" , i, size, temp
            stack.append((i, hist[i]))
    while len(stack)>0:
        temp = stack.pop()
        size = temp[1]*(i+1-temp[0])
        maxSize = max(size, maxSize)
        print "--->" , size, temp
    return maxSize

arr = [3,2,3,2,1]
print largest(arr)
