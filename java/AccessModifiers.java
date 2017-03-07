class Base{
	protected int i;

	public Base(int a){
		i = a;
	}

	public void show(){
		System.out.println(i);
	}
}

class Derived1 extends Base{
	public Derived1(int n){
		i = n;
	}
	public void show(){
		System.out.println("Hello %d",i);
	}
}

class DerivedOfDerived1 extends Derived1{
	public DerivedOfDerived1(int n){
		i = n;
	}
	public void show(){
		System.out.println("World %d", i);
	}
}
public class AccessModifiers{
	public static void main(String []args){
		Base b = new Base(10);
		Derived1 d = new Derived1(20);
		DerivedOfDerived1 d1 = new DerivedOfDerived1(30);
		b.show();
		d.show();
		d1.show();
	}
}
