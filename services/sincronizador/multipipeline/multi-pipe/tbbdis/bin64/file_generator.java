import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.RandomAccessFile;
import java.nio.MappedByteBuffer;
import java.nio.channels.FileChannel;


public class file_generator {

	/**
	 * @param args
	 * @throws IOException 
	 * @throws FileNotFoundException 
	 */
	public static void main(String[] args) throws FileNotFoundException, IOException {


		if(args.length!=2){

			System.out.println("Syntax: java file_geneartor file_path file_size");
		}
		else{

			String file = args[0];
			int length = new Integer(args[1]);
			MappedByteBuffer out = new RandomAccessFile(file, "rw").getChannel().map(FileChannel.MapMode.READ_WRITE, 0, length);
			for (int i = 0; i < length; i++)
				out.put((byte) 'x');
			System.out.println("Finished writing.\nFile: "+file+" Size: "+length+" bytes");
		}
	}

}
