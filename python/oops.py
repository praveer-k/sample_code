class MyClass(object):
    def __init__(self):
        self._some_property = "Property are nice"
        self._some_other_property = "Very Nice"
    @property
    def some_property(*args,**kwargs):
        print("calling some_property getter({0},{1},{2})".format(self,args,kwargs))
        return self._some_property
    @some_property.setter
    def some_property(*args,**kwargs):
        print("calling some_property setter({0},{1},{2})".format(self,args,kwargs))
        self._some_property = args[0]
    @property
    def some_other_property(self,*args,**kwargs):
        print("calling some_other_property getter({0},{1},{2})".format(self,args,kwargs))
        return self._some_other_property
