package datastructures;
class Node{
	public int i;
	public Node next;
	public Node(int x){
		i = x;
	}
}
public class LinkedList{
	private Node first;

	public LinkedList(){
		first = null;
	}

	public boolean isEmpty(){
		return first==null;
	}

	public void insert(int x){
		Node n = new Node(x);
		n.next = first;
		first = n;
	}

	public Node delete(){
		Node temp = first;
		first = first.next;
		return temp;
	}

	public void print(){
		Node curNode = first;
		System.out.println("List : ");
		while(curNode!=null){
			System.out.println(curNode.i);
			curNode = curNode.next;
		}
	}
}
